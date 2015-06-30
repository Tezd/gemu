#!/bin/bash
set -e
cd /var/cache/drone/src/git.sam-media.com/platform/gemu

/opt/wrapdocker &  
sleep 5

docker build -t docker.sam-media.com/gemu:latest .  
docker push docker.sam-media.com/gemu:latest

start-stop-daemon --stop --pidfile "/var/run/docker.pid"
