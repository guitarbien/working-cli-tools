#!/bin/sh -l

ls -al
#php application releasing "$1" "$2" "$3"

#time=$(date)
#echo "::set-output name=time::$time"

echo "::set-output name=tag::$3"
