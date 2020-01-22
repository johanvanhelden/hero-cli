echo
echo '========================'
echo '= Static code analysis ='
echo '========================'
php ./vendor/bin/phpstan analyse --memory-limit=2G
if [ $? != 0 ]; then
    exit 1;
fi
