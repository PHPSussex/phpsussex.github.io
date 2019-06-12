#!/bin/bash

# get directory containing this script
pushd $(dirname $0) > /dev/null
__DIR__=$(pwd -P)
popd > /dev/null

php -S localhost:8000 -t $__DIR__/../
