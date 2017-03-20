#!/bin/sh
if [ "x$1" = "x" ] ; then
	echo Analyse SVN log of some project [Need X-Win32] 
	echo Usage:
	echo          ./refresh -s project_name [-d days] 
	echo          project: yishengV2, saas
	echo          default days is 30
	echo 
	exit
fi

while getopts "s:d" arg
do
	case $arg in
		s)
			case $OPTARG in
				yishengV2)
					svn_path='/WORK/HTML/yishengV2'
					out_path='/WORK/HTML/81/svn_log/yishengV2'
					;;
				aibangmang)
					svn_path='/WORK/HTML/aibangmang'
					out_path='/WORK/HTML/81/svn_log/aibangmang'
					;;
				saas)
					svn_path='/WORK/HTML/saas'
					out_path='/WORK/HTML/81/svn_log/saas'
					;;
				crm)
					svn_path='/WORK/HTML/crm'
					out_path='/WORK/HTML/81/svn_log/crm'
					;;
				?)
					echo "Unknown Project"
					exit
					;;
			esac
			;;
		d)
			days="$OPTARG"
			;;
		?)
			echo "Unkonw param"
			exit 1	
			;;
	esac
done

if [ "x$days" = "x" ] ; then
	days="30"
fi

echo "Check Code in $svn_path"

cd $svn_path && svn log --xml -r {`date -d "$days days ago" +%Y-%m-%d`}:{`date +%Y-%m-%d`} -v > $out_path/svn.log
echo $out_path
cd $out_path && java -jar ../statsvn-0.7.0/statsvn.jar -include "controller/**:_CUSTOM_CLASS/*:_TEMPLATE/**" svn.log $svn_path

