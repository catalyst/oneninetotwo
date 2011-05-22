#!/bin/bash
echo
echo "+ Checking out current stable copies of Moodle 1.9.x and Moodle 2.0.x"

git submodule init
git submodule update

echo
echo "+ Patching 1.9.x"
echo

pushd onenine > /dev/null
patch -p1 < ../setup/patches/onenine.patch
patch -p1 < ../setup/patches/cliupgrade.patch
popd > /dev/null

