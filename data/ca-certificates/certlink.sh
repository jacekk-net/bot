#!/bin/sh

for CERTFILE in *.crt
do
  # make sure file exists and is a valid cert
  test -f "$CERTFILE" || continue
  HASH=$(openssl x509 -noout -hash -in "$CERTFILE")
  test -n "$HASH" || continue

  # use lowest available iterator for symlink
  for ITER in 0 1 2 3 4 5 6 7 8 9; do
    test -f "${HASH}.${ITER}" && continue
    cp "$CERTFILE" "${HASH}.${ITER}"
    break
    # ln -s "$CERTFILE" "${HASH}.${ITER}"
    # test -L "${HASH}.${ITER}" && break
  done
done
