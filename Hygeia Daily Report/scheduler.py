##############################
#                            #
#  Daily Report Scheduler    #
#  by Theo Nakfoor           #
#  for Hygeia Health         #
#                            #
#  updated 4/27/2020         #
#                            #
##############################

import schedule
import time
import os

def compile():
	os.system('py intake.py')
	os.system('py getReport.py')
	os.system('py getReportTX.py')
	os.system('py getReportD.py')
	os.system('py sendReport.py')

schedule.every().day.at("05:30").do(compile)

while True:
	schedule.run_pending()
	print("Waiting...")
	time.sleep(1)
