#!/bin/bash

echo " "
echo "Running build script..."
echo "---"

if [ -z $RELEASE_VERSION ] ; then
 
  # Try to get Tag version which should be created.
  if [ -z $1 ] ; then
    echo "Tag version parameter is not passed."
    echo "Determine if we have [release:{version}] shortcode to deploy new release"
    RELEASE_VERSION="$( git log -1 --pretty=%s | sed -n 's/.*\[release\:\(.*\)\].*/\1/p' )"  
  else
    echo "Tag version parameter is "$1
    RELEASE_VERSION=$1
  fi
  
else 
 
  echo "Tag version parameter is "$RELEASE_VERSION
 
fi

echo "---"

if [ -z $RELEASE_VERSION ] ; then

  echo "No [release:{tag}] shortcode found."
  echo "Finish process."
  exit 0
  
else

  echo "Determine current branch:"
  if [ -z $CIRCLE_BRANCH ]; then
    CIRCLE_BRANCH=$(git rev-parse --abbrev-ref HEAD)
  fi
  echo $CIRCLE_BRANCH
  echo "---"
    
  # Remove temp directory if it already exists to prevent issues before proceed
  if [ -d temp-build-$RELEASE_VERSION ]; then
    rm -rf temp-build-$RELEASE_VERSION
  fi
  
  echo "Create temp directory"
  mkdir temp-build-$RELEASE_VERSION
  cd temp-build-$RELEASE_VERSION
  
  echo "Do production build from scratch to temp directory"
  ORIGIN_URL="$( git config --get remote.origin.url )"
  git clone $ORIGIN_URL
  cd "$( basename `git rev-parse --show-toplevel` )"
  # Be sure we are on the same branch
  git checkout $CIRCLE_BRANCH
  echo "---"
  
  echo "Create local and remote temp branch temp-automatic-branch-"$RELEASE_VERSION
  git checkout -b temp-branch-$RELEASE_VERSION
  git push origin temp-branch-$RELEASE_VERSION
  git branch --set-upstream-to=origin/temp-branch-$RELEASE_VERSION temp-branch-$RELEASE_VERSION
  echo "---"

  echo "Be sure we do not add node and other specific files needed only for development"
  pwd
  composer install --no-dev
  composer dump-autoload
  rm -rf readme.md
  rm -rf .github
  rm -rf .gitignore
  rm -rf vendor/composer/installers
  rm -rf coverage.clover
  rm -rf ocular.phar
  rm -rf build
  rm -rf node_modules
  rm -rf composer.json
  rm -rf composer.lock
  rm -rf .scrutinizer.yml
  rm -rf circle.yml
  rm -rf build.sh
  rm -rf gruntfile.js
  rm -rf makefile
  rm -rf package.json
  rm -rf tests
  rm -rf package-lock.json
  echo "Be sure we do not add .git directories"
  find ./vendor -name .git -exec rm -rf '{}' \;
  echo "Be sure we do not add .svn directories"
  find ./vendor -name .svn -exec rm -rf '{}' \;
  echo "Git Add"
  git add --all
  echo "Be sure we added vendor directory"
  git add -f vendor
  echo "---"
  
  echo "Now commit our build to remote branch"
  git commit -m "[ci skip] Distributive Auto Build" --quiet
  git pull
  git push --quiet
  echo "---"

  echo "Finally, create tag "$RELEASE_VERSION
  git tag -a $RELEASE_VERSION -m "v"$RELEASE_VERSION" - Distributive Auto Build"
  git push origin $RELEASE_VERSION
  echo "---"

  echo "Remove local and remote temp branches, but switch to previous branch before"
  git checkout $CIRCLE_BRANCH
  git push origin --delete temp-branch-$RELEASE_VERSION
  git branch -D temp-branch-$RELEASE_VERSION
  echo "---"
  
  # Remove temp directory.
  echo "Remove temp directory"
  cd ../..
  rm -rf temp-build-$RELEASE_VERSION
  echo "---"
  
  echo "Done"

fi 
