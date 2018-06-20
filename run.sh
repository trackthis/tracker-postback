#!/usr/bin/env bash

# Clean up old fifos
rm fifo.* 2>/dev/null

test -n "$PORT" || PORT=5000
XRARGS="-f -x -s http:0.0.0.0:$PORT"

if [ -f tcpd.c ] && command -v tcc &>/dev/null; then # "nc -e", run live
  tcc -run tcpd.c $PORT php web/index.php
elif [ -f tcpd.c ] && command -v gcc &>/dev/null; then # "nc -e", compile first
  gcc tcpd.c -o tcpd && ./tcpd $PORT php web/index.php
elif command -v xr &>/dev/null; then # fifo NC + LB
  for i in {0..9} ; do
    XRARGS="$XRARGS -b 127.0.0.1:800$i"
    echo Starting handler on 800$i
    { while true ; do FIFO=$(hexdump -n 16 -v -e '/1 "%02X"' -e '/16 "\n"' /dev/urandom) ; mkfifo fifo.$FIFO ; nc -l -p 800$i < fifo.$FIFO | php web/index.php > fifo.$FIFO ; rm fifo.$FIFO ; done } &
  done;
  echo Starting load-balancer on $PORT
  echo xr $XRARGS
  xr $XRARGS
else # Single fifo NC
  echo Starting on $PORT
  while true ; do FIFO=$(hexdump -n 16 -v -e '/1 "%02X"' -e '/16 "\n"' /dev/urandom) ; mkfifo fifo.$FIFO ; nc -l -p $PORT < fifo.$FIFO | php web/index.php > fifo.$FIFO ; rm fifo.$FIFO ; done
fi
