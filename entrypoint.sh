#!/bin/sh -l

echo "Hello $1 $2 $3"
ls -al
#php application releasing master "$1" "$2" "$3"

#time=$(date)
#echo "::set-output name=time::$time"

echo "::set-output name=tag::$3"
