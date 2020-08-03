#!/bin/bash

echo '============================='
echo '== Running code validation =='
echo '============================='

echo
echo '============================='
echo '| Syntax errors:             '
echo '============================='
find app tests config -name "*.php" -print0 | xargs -0 -n1 -P8 php -l
if [ $? != 0 ]; then
    exit 1;
fi

echo
echo '============================='
echo '| Coding standard:           '
echo '============================='
phpcs --standard=phpcs.xml -vps
if [ $? != 0 ]; then
    exit 1;
fi

echo
echo '============================='
echo '| Coding standard fixers:    '
echo '============================='
php-cs-fixer fix app config tests --dry-run --diff --allow-risky=yes --config=.php_cs
if [ $? != 0 ]; then
    exit 1;
fi

echo
echo '============================='
echo '| Mess detector:             '
echo '============================='
phpmd app text phpmd.xml
if [ $? != 0 ]; then
    exit 1;
fi

echo
echo '============================='
echo '| Copy paste detector:       '
echo '============================='
phpcpd app config tests
if [ $? != 0 ]; then
    exit 1;
fi
echo
