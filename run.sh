#!/usr/bin/env bash

rm fifo.* 2>/dev/null

test -v "$PORT" || PORT=5000
XRARGS="-f -x -s http:0.0.0.0:$PORT"

# Heroku environment
if [ -d "vendor/bin/heroku-php-apache2" ]; then
  exec vendor/bin/heroku-php-apache2 web/
fi

## Use a load-balancer if possible
#if command -v xr >/dev/null 2>/dev/null; then
#  for i in {0..9} ; do
#    XRARGS="$XRARGS -b 127.0.0.1:800$i"
#    echo Starting handler on 800$i
#    { while true ; do FIFO=$(hexdump -n 16 -v -e '/1 "%02X"' -e '/16 "\n"' /dev/urandom) ; mkfifo fifo.$FIFO ; nc -l -p 800$i < fifo.$FIFO | php web/index.php > fifo.$FIFO ; rm fifo.$FIFO ; done } &
#  done;
#  echo Starting load-balancer on $PORT
#  echo xr $XRARGS
#  xr $XRARGS
#else
  echo Starting on $PORT
  while true ; do FIFO=$(hexdump -n 16 -v -e '/1 "%02X"' -e '/16 "\n"' /dev/urandom) ; mkfifo fifo.$FIFO ; nc -l -p $PORT < fifo.$FIFO | php web/index.php > fifo.$FIFO ; rm fifo.$FIFO ; done
#fi
