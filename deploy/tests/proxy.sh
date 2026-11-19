#!/bin/sh
# Run on a Linux Docker host before the normal Compose stack claims its subnet.
set -eu
cd "$(dirname "$0")/../.."
tag=${IMAGE_TAG:-validation}
python=${PYTHON:-python3}
source_path=$(pwd)
if command -v cygpath >/dev/null 2>&1; then source_path=$(cygpath -m "$source_path"); fi
prefix="atlas-proxy-test-$$"
network="$prefix-ingress"
cleanup() {
    docker rm -f "$prefix-nginx" "$prefix-app" >/dev/null 2>&1 || true
    docker network rm "$network" >/dev/null 2>&1 || true
}
trap cleanup EXIT HUP INT TERM
docker network create --subnet 172.30.88.0/29 "$network" >/dev/null
docker run -d --name "$prefix-app" --network "$network" --network-alias app --ip 172.30.88.5 \
    --mount "type=bind,src=$source_path/deploy/tests/index.php,dst=/var/www/html/public/index.php,readonly" \
    --entrypoint php-fpm "atlas-dashboard-app:$tag" -F >/dev/null
docker run -d --name "$prefix-nginx" --network "$network" --ip 172.30.88.2 \
    -p 127.0.0.1::8080 "atlas-dashboard-nginx:$tag" >/dev/null
docker exec "$prefix-nginx" nginx -t
port=$(docker port "$prefix-nginx" 8080/tcp | sed 's/.*://')
# Wait for FPM without sleeping indefinitely.
attempt=0
until curl -fsS "http://127.0.0.1:$port/index.php" > /dev/null; do
    attempt=$((attempt + 1))
    test "$attempt" -lt 30
    sleep 1
done
check() {
    expected=$1
    payload=$2
    printf '%s' "$payload" | "$python" -c '
import json, sys
value = json.load(sys.stdin)
client, https, scheme, port = sys.argv[1:]
assert value == dict(client=client, https=https, scheme=scheme, port=port, xff="", forwarded_proto=""), value
' $expected
}
# The Docker gateway reached through the loopback publish cannot assert a CF IP.
direct=$(curl -fsS -H 'CF-Connecting-IP: 198.51.100.77' -H 'X-Forwarded-For: 198.51.100.88' \
    -H 'X-Forwarded-Proto: https' "http://127.0.0.1:$port/index.php")
printf '%s' "$direct" | "$python" -c 'import json,sys; v=json.load(sys.stdin); assert v["client"] not in ("198.51.100.77", "198.51.100.88"); assert v["https"] == "off" and v["scheme"] == "http" and v["xff"] == "" and v["forwarded_proto"] == "", v'
# An arbitrary neighbor on the same network is also untrusted.
neighbor=$(docker run --rm --network "$network" --ip 172.30.88.4 curlimages/curl:latest \
    -fsS -H 'CF-Connecting-IP: 198.51.100.77' -H 'X-Forwarded-Proto: https' http://172.30.88.2:8080/index.php)
check '172.30.88.4 off http 8080' "$neighbor"
# The connector may assert distinct visitors; arbitrary XFF/proto are discarded.
for visitor in 198.51.100.11 203.0.113.22; do
    trusted=$(docker run --rm --network "$network" --ip 172.30.88.3 curlimages/curl:latest \
        -fsS -H "CF-Connecting-IP: $visitor" -H 'X-Forwarded-For: 192.0.2.99' \
        -H 'X-Forwarded-Proto: http' http://172.30.88.2:8080/index.php)
    check "$visitor on https 443" "$trusted"
done
echo 'Proxy identity and HTTPS boundary checks passed.'
