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

if [ -f "$HOME/configfiles/.php_cs" ]; then
    echo
    echo '============================='
    echo '| Coding standard fixers:    '
    echo '============================='
    php-cs-fixer fix app config tests --dry-run --diff --allow-risky=yes --config=$HOME/configfiles/.php_cs
    if [ $? != 0 ]; then
        exit 1;
    fi
fi

echo
echo '============================='
echo '| Missing docblocks:         '
echo '============================='
hasMissing=false

# iterate over all methods in our codebase and their previous line as well (this should be the docblock closing tag)
while read -r line; do
    # skip grep separators
    if [ "$line" == '--' ]; then
        continue
    fi

    # skip docblock closing tags
    if [[ "$line" =~ '*/' ]]; then
        continue
    fi

    # skip the method line itself
    if [[ "$line" =~ 'function' ]]; then
        continue
    fi

    # any left-over lines are not closing docblock tags and thus methods without docblocks
    hasMissing=true
    echo "Missing docblock detected: $line"
done <<<$(grep -r -B 1 --include=*.php ".*function.*)$" app tests)

if $hasMissing; then
    exit 1;
fi

if [ -f "phpmd.xml" ]; then
    echo
    echo '============================='
    echo '| Mess detector:             '
    echo '============================='
    phpmd app text phpmd.xml
    if [ $? != 0 ]; then
        exit 1;
    fi
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
