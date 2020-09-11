##############################
#                            #
#  Daily Report Auto Query   #
#  by Theo Nakfoor           #
#  for Hygeia Health         #
#                            #
#  updated 4/27/2020         #
#                            #
##############################

import mysql.connector
from datetime import datetime, timedelta
import xlsxwriter

# Define dates for query ranges
today = datetime.now()
tomorrow = datetime.now() + timedelta(days=1)
yesterday = datetime.now() + timedelta(days=-1)

thisMonth = today.strftime("%Y-%m-01")

cnx = mysql.connector.connect(user="#########", password="##########",
                              host="#############",
                              database="############")
cursor = cnx.cursor()

# Get daily apps
q1 = ("SELECT Secuser.FirstName, Secuser.LastName, COUNT(*) FROM SO, Secuser, Patient WHERE SO.MktRepKey IN (104, 110, 111, 125) AND SO.MktRepKey = Secuser.MktRepKey AND SO.PtKey = Patient.PtKey AND Patient.CreateDt >= '20" + yesterday.strftime("%y-%m-%d") + "' GROUP BY Secuser.FirstName")
cursor.execute(q1)
result = cursor.fetchall()

dailyApps = []
for x in result:
        dailyApps.append([x[0], x[1], x[2]])

if(len(dailyApps) < 4):
    for x in range(4-len(dailyApps)):
        dailyApps.append([0,0,0])

# End query 1

# Get apps up to day
q2 = ("SELECT DISTINCT Secuser.FirstName, Secuser.LastName, COUNT(*) FROM Secuser, Patient, Doctor WHERE Doctor.MktRepKey IN (104, 110, 111, 125) AND Patient.OrderingDocKey = Doctor.DocKey AND Doctor.MktRepKey = Secuser.MktRepKey AND Patient.CreateDt > '" + thisMonth + "' GROUP BY Secuser.FirstName")
cursor.execute(q2)
result2 = cursor.fetchall()

appsToDate = []
for x in result2:
    appsToDate.append([x[0], x[1], x[2]])
# End query 2

q3 = ("SELECT Secuser.FirstName, Secuser.LastName, COUNT(*) FROM SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey), Secuser, Patient WHERE SO.MktRepKey IN (104, 110, 111, 125) AND SO.PtKey = Patient.PtKey AND SO.MktRepKey = Secuser.MktRepKey AND Patient.CreateDt >= '" + thisMonth + "' and CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') GROUP BY Secuser.FirstName")
cursor.execute(q3)
result3 = cursor.fetchall()

bucket1Apps = []
for x in result3:
    bucket1Apps.append([x[0], x[1], x[2]])


bucket2Apps = []
for x in range(4):
    apps = appsToDate[x][2] - bucket1Apps[x][2]
    bucket2Apps.append([appsToDate[x][0], appsToDate[x][1], apps])

q4 = ("SELECT DISTINCT Secuser.FirstName, Secuser.LastName, COUNT(*) FROM Secuser, Patient, Doctor, PtPayor, PayorRule WHERE Doctor.MktRepKey IN (104, 110, 111, 125) AND Patient.OrderingDocKey = Doctor.DocKey AND Doctor.MktRepKey = Secuser.MktRepKey AND Patient.PtKey = PtPayor.PtKey and PtPayor.PayorKey = PayorRule.PayorKey and PayorRule.isCommercial = 1 and Patient.CreateDt > '" + thisMonth + "' GROUP BY Secuser.FirstName")
cursor.execute(q4)
result4 = cursor.fetchall()

commercialApps = []
for x in result4:
    commercialApps.append([x[0], x[1], x[2]])

q5 = ("SELECT DISTINCT Secuser.FirstName, Secuser.LastName, COUNT(*) FROM Secuser, Patient, Doctor, PtPayor, PayorRule WHERE Doctor.MktRepKey IN (104, 110, 111, 125) AND Patient.OrderingDocKey = Doctor.DocKey AND Doctor.MktRepKey = Secuser.MktRepKey AND Patient.PtKey = PtPayor.PtKey and PtPayor.PayorKey = PayorRule.PayorKey and PayorRule.isMedicaid = 1 and Patient.CreateDt > '" + thisMonth + "' GROUP BY Secuser.FirstName")
cursor.execute(q5)
result5 = cursor.fetchall()

nonCommApps = []
for x in result5:
    nonCommApps.append([x[0], x[1], x[2]])


print(" ")
print("Total Apps")
print(" ")
for x in appsToDate:
    print(x)
print(" ")
print("Bucket 1 Apps")
print(" ")
for x in bucket1Apps:
    print(x)
print(" ")
print("Bucket 2 Apps")
print(" ")
for x in bucket2Apps:
    print(x)
print(" ")
print("Daily Apps")
print(" ")
for x in dailyApps:
    print(x)
print(" ")
print("Commercial Apps")
print(" ")
for x in commercialApps:
    print(x)
print(" ")
print("Medi-Cal Apps")
print(" ")
for x in nonCommApps:
    print(x)

workbook = xlsxwriter.Workbook('C:/Users/hygadmin\Desktop/Hygeia Daily Report/reports/California/DailyReportCA' + today.strftime("%Y-%m-%d") + '.xlsx')
worksheet = workbook.add_worksheet()

percentFormat = workbook.add_format({'align':'center'})
percentFormat.set_num_format(10)

percentFormat2 = workbook.add_format({'bg_color': '#FFFFE0', 'align':'center'})
percentFormat2.set_num_format(10)

merge_format = workbook.add_format({
    'bold': 1,
    'border': 1,
    'align': 'center',
    'underline': 1,
    'bg_color': '#afeeee'})

worksheet.write(0, 3, "Bucket 1", workbook.add_format({'underline': True, 'align' : 'center', 'bg_color': '#afeeee'}))
worksheet.write(0, 4, "", workbook.add_format({'underline': True, 'align' : 'center', 'bg_color': '#afeeee'}))
worksheet.merge_range("D1:E1", "Bucket 1", merge_format)
worksheet.write(0, 5, "Bucket 2", workbook.add_format({'underline': True, 'align' : 'center', 'bg_color': '#afeeee'}))
worksheet.write(0, 6, "", workbook.add_format({'underline': True, 'align' : 'center', 'bg_color': '#afeeee'}))
worksheet.merge_range("F1:G1", "Bucket 2", merge_format)
worksheet.merge_range("J1:M1", "Payer Mix", merge_format)

worksheet.write(1, 0, "Market Rep First Name", workbook.add_format({'bold': True, 'bg_color': 'black', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 0, 25)
worksheet.write(1, 1, "Market Rep Last Name", workbook.add_format({'bold': True, 'bg_color': 'black', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 1, 300)
worksheet.write(1, 2, "Total Apps", workbook.add_format({'bold': True, 'bg_color': 'green', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 2, 15)
worksheet.write(1, 3, "OB Originated Apps", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 3, 50)
worksheet.write(1, 4, "Percent OB origin", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 4, 50)
worksheet.write(1, 5, "Non-OB Apps",workbook.add_format({'bold': True, 'bg_color': '#FFFF00', 'font_color': 'black', 'text_wrap':1}))
worksheet.set_column(1, 5, 15)
worksheet.write(1, 6, "Percent Non-OB", workbook.add_format({'bold': True, 'bg_color': '#FFFF00', 'font_color': 'black', 'text_wrap':1}))
worksheet.set_column(1, 6, 50)
worksheet.write(1, 7, "Daily Apps (20" + yesterday.strftime("%y/%m/%d") + ")", workbook.add_format({'bold': True, 'bg_color': 'black', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 7, 15)
worksheet.write(1, 8, "Projected Apps", workbook.add_format({'bold': True, 'bg_color': 'black', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 8, 20)
worksheet.write(1, 9, "Commercial Apps", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 9, 25)
worksheet.write(1, 10, "Commercial %", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 10, 25)
worksheet.write(1, 11, "Medi-Cal Apps", workbook.add_format({'bold': True, 'bg_color': '#FFFF00', 'font_color': 'black', 'text_wrap':1}))
worksheet.set_column(1, 11, 25)
worksheet.write(1, 12, "Medi-Cal %", workbook.add_format({'bold': True, 'bg_color': '#FFFF00', 'font_color': 'black', 'text_wrap':1}))
worksheet.set_column(1, 12, 25)
worksheet.write(1, 13, "Goal", workbook.add_format({'bold': True, 'bg_color': 'black', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 13, 15)
worksheet.write(1, 14, "Difference", workbook.add_format({'bold': True, 'bg_color': 'black', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 14, 15)
worksheet.write(1, 15, "Attainment", workbook.add_format({'bold': True, 'bg_color': 'black', 'font_color': 'white', 'text_wrap':1}))
worksheet.set_column(1, 15, 15)

row = 2
col = 0
for x in range(4):
    worksheet.write(row, col, appsToDate[x][0])
    worksheet.write(row, col+1, appsToDate[x][1])
    worksheet.write(row, col+2,appsToDate[x][2], workbook.add_format({'bg_color': '#98FB98', 'align': 'center'}))
    worksheet.write(row, col+3,bucket1Apps[x][2], workbook.add_format({'align': 'center'}))
    worksheet.write_formula(row, col+4, "=D" + str(row+1) + "/C" + str(row+1), workbook.add_format({'align':'center', 'num_format':'0.00%'}))
    worksheet.write(row, col+5, bucket2Apps[x][2], workbook.add_format({'bg_color': '#FFFFE0', 'align': 'center'}))
    worksheet.write_formula(row, col+6, "=F" + str(row+1) + "/C" + str(row+1), workbook.add_format({'align':'center', 'num_format':'0.00%'}))
    worksheet.write(row, col+7,  dailyApps[x][2], workbook.add_format({'align': 'center'}))
    worksheet.write(row, col+8, " ")
    worksheet.write(row, col+9, commercialApps[x][2], workbook.add_format({'align': 'center'}))
    commPercent = round(commercialApps[x][2]/appsToDate[x][2], 4)
    worksheet.write(row, col+10, commPercent, percentFormat)
    worksheet.write(row, col+11, nonCommApps[x][2], workbook.add_format({'bg_color': '#FFFFE0', 'align': 'center'}))
    mediCalPercent = round(nonCommApps[x][2]/appsToDate[x][2], 4)
    worksheet.write(row, col+12, mediCalPercent, percentFormat2)
    worksheet.write(row, col+13, " ")
    worksheet.write(row, col+14, " ")
    worksheet.write(row, col+15, " ")
    row+=1

worksheet.write("A7", "Totals", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("B7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write_formula("C7", "=SUM(C3:C6)", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write_formula("D7", "=SUM(D3:D6)", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("E7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write_formula("F7", "=SUM(F3:F6)", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("G7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write_formula("H7", "=SUM(H3:H6)", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("I7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write_formula("J7", "=SUM(J3:J6)", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("K7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write_formula("L7", "=SUM(L3:L6)", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("M7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("N7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("O7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))
worksheet.write("P7", " ", workbook.add_format({'bg_color': 'black', 'color':'white', 'bold':True, 'align': 'center'}))

worksheet.write("C9", "Total", workbook.add_format({'bold':True, 'align': 'center'}))
worksheet.write_formula("D9", "=D7/C7", workbook.add_format({'bold':True, 'align': 'center', 'num_format':'0.00%'}))

worksheet.write_formula("K7", "=J7/C7", workbook.add_format({'bold':True, 'align': 'center', 'num_format':'0.00%', 'bg_color':'black', 'color':'white'}))
worksheet.write_formula("M7", "=L7/C7", workbook.add_format({'bold':True, 'align': 'center', 'num_format':'0.00%', 'bg_color':'black', 'color':'white'}))

worksheet.write("F9", "Total", workbook.add_format({'bold':True, 'align': 'center'}))
worksheet.write_formula("G9", "=F7/C7", workbook.add_format({'bold':True, 'align': 'center', 'num_format':'0.00%'}))
workbook.close()



cursor.close()
cnx.close()
