# This is an executable file used for the SystemTest.php
if [ "$1" = "fail" ]
then
	echo "This probably failed. Or so."
	exit 42
elif [ "$1" = "something" ]
then
	echo "Something is sometimes not that cool. But anyway."
	exit 0
else
	echo "Some dummy output just to test execution of this file."
	exit 0
fi
