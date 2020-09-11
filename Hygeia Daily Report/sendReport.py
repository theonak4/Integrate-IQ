##############################
#                            #
#  Daily Report Auto Email   #
#  by Theo Nakfoor           #
#  for Hygeia Health         #
#                            #
#  updated 4/27/2020         #
#                            #
##############################

from datetime import datetime
from postmarker.core import PostmarkClient

today = datetime.now()

postmark = PostmarkClient(server_token='###############################')

#########################################################################
californiaEmail = postmark.emails.Email(
    From='#############################',
    To='##########################',
    Subject='California Daily Report ' + today.strftime("%Y-%m-%d"),
    HtmlBody='<html><body><strong>This a Hygeia automated email.</strong> Please email ########################## for questions.</body></html>'
)

californiaEmail.attach('/Hygeia Daily Report/reports/California/DailyReportCA' + today.strftime("%Y-%m-%d") + '.xlsx')
californiaEmail.attach('/Hygeia Daily Report/reports/Texas/DailyReportTX' + today.strftime("%Y-%m-%d") + '.xlsx')
californiaEmail.send()

print("Email sent (California).")
#########################################################################
texasEmail = postmark.emails.Email(
    From='############################',
    To='#######################',
    Subject='Texas Daily Report ' + today.strftime("%Y-%m-%d"),
    HtmlBody='<html><body><strong>This a Hygeia automated email.</strong> Please email ########################## for questions.</body></html>'
)

texasEmail.attach('/Hygeia Daily Report/reports/Texas/DailyReportTX' + today.strftime("%Y-%m-%d") + '.xlsx')
texasEmail.attach('/Hygeia Daily Report/reports/California/DailyReportCA' + today.strftime("%Y-%m-%d") + '.xlsx')
texasEmail.send()

print("Email sent (Texas).")
#########################################################################
executiveEmail = postmark.emails.Email(
    From='#############################',
    To='########################',
    Subject='Daily Reports ' + today.strftime("%Y-%m-%d"),
    HtmlBody='<html><body><strong>This a Hygeia automated email.</strong> Please email ########################## for questions.</body></html>'
)

executiveEmail.attach('/Hygeia Daily Report/reports/California/DailyReportCA' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail.attach('/Hygeia Daily Report/reports/Texas/DailyReportTX' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail.attach('/Hygeia Daily Report/reports/Intake/Intake Report ' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail.attach('/Hygeia Daily Report/reports/3Bucket/3BucketReport' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail.send()

print("Email sent (Executive 1).")
#########################################################################
executiveEmail2 = postmark.emails.Email(
    From='###############################',
    To='###############################',
    Subject='Daily Reports ' + today.strftime("%Y-%m-%d"),
    HtmlBody='<html><body><strong>This a Hygeia automated email.</strong> Please email ########################## for questions.</body></html>'
)

executiveEmail2.attach('/Hygeia Daily Report/reports/California/DailyReportCA' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail2.attach('/Hygeia Daily Report/reports/Texas/DailyReportTX' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail2.attach('/Hygeia Daily Report/reports/Intake/Intake Report ' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail2.attach('/Hygeia Daily Report/reports/3Bucket/3BucketReport' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail2.send()

print("Email sent (Executive 2).")
#########################################################################
executiveEmail3 = postmark.emails.Email(
    From='#################################',
    To='########################',
    Subject='Daily Reports ' + today.strftime("%Y-%m-%d"),
    HtmlBody='<html><body><strong>This a Hygeia automated email.</strong> Please email ########################## for questions.</body></html>'
)

executiveEmail3.attach('/Hygeia Daily Report/reports/California/DailyReportCA' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail3.attach('/Hygeia Daily Report/reports/Texas/DailyReportTX' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail3.attach('/Hygeia Daily Report/reports/Intake/Intake Report ' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail3.attach('/Hygeia Daily Report/reports/3Bucket/3BucketReport' + today.strftime("%Y-%m-%d") + '.xlsx')
executiveEmail3.send()

print("Email sent (Executive 3).")
#########################################################################
robEmail = postmark.emails.Email(
    From='###########################',
    To='###################### ',
    Subject='Daily 3 Bucket Report ' + today.strftime("%Y-%m-%d"),
    HtmlBody='<html><body><strong>This a Hygeia automated email.</strong> Please email ########################## for questions.</body></html>'
)

robEmail.attach('/Hygeia Daily Report/reports/California/DailyReportCA' + today.strftime("%Y-%m-%d") + '.xlsx')
robEmail.attach('/Hygeia Daily Report/reports/Texas/DailyReportTX' + today.strftime("%Y-%m-%d") + '.xlsx')
robEmail.attach('/Hygeia Daily Report/reports/3Bucket/3BucketReport' + today.strftime("%Y-%m-%d") + '.xlsx')
robEmail.attach('/Hygeia Daily Report/reports/Intake/Intake Report ' + today.strftime("%Y-%m-%d") + '.xlsx')
robEmail.send()

print("Email sent.")
