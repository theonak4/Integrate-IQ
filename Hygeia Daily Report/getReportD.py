##############################
#                            #
#  3 Bucket Auto Report      #
#  by Theo Nakfoor           #
#  for Hygeia Health         #
#                            #
#  FIRST WORKING DYNAMIC     #
#  VERSION          v2.1     #
#  5/26/2020                 #
##############################

import mysql.connector
from datetime import datetime, timedelta
import xlsxwriter
import sys


# Define dates for query ranges
thisMonth = datetime.now()
nextMonth = datetime.now() + timedelta(days=30)

startDate = thisMonth.strftime("%Y-%m-01")
endDate = nextMonth.strftime("%Y-%m-01")

if(len(sys.argv) > 1):
    startDate = sys.argv[1]
    endDate = sys.argv[2]

cnx = mysql.connector.connect(user="#########", password="##########",
                              host="#############",
                              database="############")
cursor = cnx.cursor()

# Bucket 1
b1normal = ("select CF3, SOStatus, count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) where SOCreateDt > '"+ startDate +"' and SOCreateDt < '"+ endDate +"' and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3,SOStatus")
cursor.execute(b1normal)
res1 = cursor.fetchall()

b1F = ["Moms Get More", "OB Office - EMR", "OB Office - Text", "OB Office- Brochure", "OB Office- Tear Sheet", "OB portal"]
b1R = [["Moms Get More", 0], ["OB Office - EMR", 1], ["OB Office - Text", 2], ["OB Office- Brochure", 3], ["OB Office- Tear Sheet", 4], ["OB portal", 5]]
for x in res1:
    indx = 0
    for y in b1R:
        if(x[0] == y[0]):
            indx = y[1]
    if(x[1] == "New"):
        b1F[indx] = [x[0], x[2]]
    elif(x[1] == "Closed"):
        if(str(b1F[indx][1]).isdigit()):
            b1F[indx] = [x[0], b1F[indx][1], x[2]]
        else:
            b1F[indx] = [x[0], 0, x[2]]
    elif(x[2] == "Delivered"):
        b1F[indx] = [x[0], b1F[indx][1], b1F[indx][2]+x[2]]

b1voided = ("select CF3, 'Voided', count(*) from SOVoid join SOVoidDtl using (SOVoidKey) join Item using (ItemKey) join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey) where CreateDt > '"+ startDate +"' and CreateDt < '"+ endDate +"' and ItemGroup = 'Breast Pumps' and CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3")
cursor.execute(b1voided)
res2 = cursor.fetchall()

for x in res2:
    indx = 0
    for y in b1R:
        if(x[0] == y[0]):
            indx = y[1]
    b1F[indx] = [b1F[indx][0], b1F[indx][1], b1F[indx][2], x[2]]
# End Bucket 1

# Bucket 2
b2normal = ("select CF3, SOStatus, count(*) from SO left join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) join Doctor on (SO.OrderingDocKey=Doctor.DocKey) where SOCreateDt > '"+ startDate +"' and SOCreateDt < '"+ endDate +"' and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and Doctor.MktRepKey is not null and Doctor.MktRepKey not in (101,106,107) and CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3,SOStatus;")
cursor.execute(b2normal)
res3 = cursor.fetchall()

b2F = ["Ads", "Facebook", "FB", "Google", "HH", "Insurance gave phone #", "Ovia", "Tiburon", "WWW", "Zeeto"]
b2R = [["Ads", 0], ["Facebook", 1], ["FB", 2], ["Google", 3], ["HH", 4], ["Insurance gave phone #", 5], ["Ovia", 6], ["Tiburon", 7], ["WWW", 8], ["Zeeto", 9]]

b2n = []
for x in res3:
    b2n.append([x[0], x[1], x[2]])
b2n.append(["", "", ""])

for x in range(len(b2n)):
    if(b2n[x][1] == "New" and b2n[x+1][1] == "New"):
        b2n.insert(x+1, [b2n[x][0], "Closed", 0])
    elif(b2n[x][1] == "Closed" and b2n[x+1][1] == "Closed"):
        b2n.insert(x+1, [b2n[x+1][0], "New", 0]) # Clean up results
    elif(b2n[x][1] == "Delivered" and b2n[x+1][1] == "Closed"):
        b2n.insert(x+1, [b2n[x+1][0], "New", 0])
    elif(b2n[x][1] == "New" and b2n[x+1][1] == "Closed"):
        continue

for x in b2n:
    indx = 0
    for y in b2R:
        if(x[0] == y[0]):
            indx = y[1]
    if(x[1] == "New"):
        b2F[indx] = [x[0], x[2]]
    elif(x[1] == "Closed"):
        if(str(b2F[indx][1]).isdigit()):
            b2F[indx] = [x[0], b2F[indx][1], x[2]]
        else:
            b2F[indx] = [x[0], 0, x[2]]
    elif(x[2] == "Delivered"):
        b2F[indx] = [x[0], b2F[indx][1], b2F[indx][2]+x[2]]

b2voided = ("select CF3, 'Voided', count(*) from SOVoid join SOVoidDtl using (SOVoidKey) join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey) join Item using (ItemKey) join Patient on (SOVoid.PtKey=Patient.PtKey) join Doctor on (Patient.OrderingDocKey=Doctor.DocKey) where SOVoid.CreateDt > '"+ startDate +"' and SOVoid.CreateDt < '"+ endDate +"' and ItemGroup = 'Breast Pumps' and Doctor.MktRepKey is not null and Doctor.MktRepKey not in (101,106,107) and CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3;")
cursor.execute(b2voided)
res4 = cursor.fetchall()

for x in res4:
    indx = 0
    for y in b2R:
        if(x[0] == y[0]):
            indx = y[1]
    b2F[indx] = [b2F[indx][0], b2F[indx][1], b2F[indx][2], x[2]]

indx = 0
for x in b2F:
    indx+=1
    if isinstance(x, list):
        continue
    else:
        b2F[indx-1] = [b2R[indx-1][0], 0, 0, 0]

indx = 0

for x in b2F:
    indx+=1
    if(len(x) < 4):
        if(len(x) < 3):
            b2F[indx-1] = [b2F[indx-1][0], b2F[indx-1][1], 0, 0]
        else:
            b2F[indx-1] = [b2F[indx-1][0], b2F[indx-1][1], b2F[indx-1][2], 0]
# End Bucket 2

# Bucket 3
b3normal = ("select CF3, SOStatus, count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) join Doctor on (SO.OrderingDocKey=Doctor.DocKey) where SOCreateDt > '"+ startDate +"' and SOCreateDt < '"+ endDate +"' and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and (Doctor.MktRepKey is null or Doctor.MktRepKey in (101,106,107)) and CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3,SOStatus;")
cursor.execute(b3normal)
res5 = cursor.fetchall()

b3F = ["Ads", "Facebook", "FB", "Google", "HH", "Insurance gave phone #", "Ovia", "Tiburon", "WWW", "Zeeto"]
b3R = [["Ads", 0], ["Facebook", 1], ["FB", "2"], ["Google", 3], ["HH", 4], ["Insurance gave phone #", 5], ["Ovia", 6], ["Tiburon", 7], ["WWW", 8], ["Zeeto", 9]]

b3n = []
for x in res5:
    b3n.append([x[0], x[1], x[2]])
b3n.append(["", "", ""])

for x in range(len(b3n)):
    if(b3n[x][1] == "New" and b3n[x+1][1] == "New"):
        b3n.insert(x+1, [b3n[x][0], "Closed", 0])
    elif(b3n[x][1] == "Closed" and b3n[x+1][1] == "Closed"):
        b3n.insert(x+1, [b3n[x+1][0], "New", 0]) # Clean up results
    elif(b3n[x][1] == "Delivered" and b3n[x+1][1] == "Closed"):
        b3n.insert(x+1, [b3n[x+1][0], "New", 0])
    elif(b3n[x][1] == "New" and b3n[x+1][1] == "Closed"):
        continue

for x in b3n:
    indx = 0
    for y in b3R:
        if(x[0] == y[0]):
            indx = y[1]
    if(x[1] == "New"):
        b3F[indx] = [x[0], x[2]]
    elif(x[1] == "Closed"):
        if(str(b3F[indx][1]).isdigit()):
            b3F[indx] = [x[0], b3F[indx][1], x[2]]
        else:
            b3F[indx] = [x[0], 0, x[2]]
    elif(x[2] == "Delivered"):
        b3F[indx] = [x[0], b3F[indx][1], b3F[indx][2]+x[2]]

b3voided = ("select CF3, 'Voided', count(*) from SOVoid join SOVoidDtl using (SOVoidKey) join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey) join Item using (ItemKey) join Patient on (SOVoid.PtKey=Patient.PtKey) join Doctor on (Patient.OrderingDocKey=Doctor.DocKey) where SOVoid.CreateDt > '"+ startDate +"' and SOVoid.CreateDt < '"+ endDate +"' and ItemGroup = 'Breast Pumps' and (Doctor.MktRepKey is null or Doctor.MktRepKey in (101,106,107)) and CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3;")
cursor.execute(b3voided)
res6 = cursor.fetchall()

for x in res6:
    indx = 0
    for y in b3R:
        if(x[0] == y[0]):
            indx = y[1]
    if isinstance(b3F[indx], list):
        b3F[indx] = [b3F[indx][0], b3F[indx][1], b3F[indx][2], x[2]]
    else:
        b3F[indx] = [b3R[indx][0], 0, 0, x[2]]

indx = 0
for x in b3F:
    indx+=1
    if isinstance(x, list):
        continue
    else:
        b3F[indx-1] = [b3R[indx-1][0], 0, 0, 0]

indx = 0
for x in b3F:
    indx+=1
    if(len(x) < 4):
        if(len(x) < 3):
            b3F[indx-1] = [b3F[indx-1][0], b3F[indx-1][1], 0, 0]
        else:
            b3F[indx-1] = [b3F[indx-1][0], b3F[indx-1][1], b3F[indx-1][2], 0]

totalShippedQ = ("select count(*) from SO where SOActualDeliveryDt >= '"+ startDate +"' and SOActualDeliveryDt <= '"+ endDate +"' and date(SOActualDeliveryDt) >= date(SOCreateDt) and SOStatus in ('Closed','Delivered') and SOClassification in ('New Pump Order','Non-Hygeia Pump Order');")
cursor.execute(totalShippedQ)
res7 = cursor.fetchall()

totalShipped = 0
for x in res7:
    totalShipped = x[0]


workbook = xlsxwriter.Workbook('C:/Users/hygadmin/Desktop/Hygeia Daily Report/reports/3Bucket/3BucketReport' + thisMonth.strftime("%Y-%m-%d") + '.xlsx')
worksheet = workbook.add_worksheet()

# Write Header
worksheet.write(0, 0, "Bucket", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.set_column(0, 0, 5)
worksheet.write(0, 1, "Leadsource", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.set_column(0, 1, 20)
worksheet.write(0, 2, "New", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write(0, 3, "Shipped", workbook.add_format({'bold': True, 'bg_color': '#90EE90', 'font_color': 'white'}))
worksheet.write(0, 4, "Voided", workbook.add_format({'bold': True, 'bg_color': '#fed8b1', 'font_color': 'white'}))
worksheet.write(0, 5, "Total", workbook.add_format({'bold': True, 'bg_color': '#add8e6', 'font_color': 'white'}))

row=1
col=0

for x in b1F:
    worksheet.write(row, col, "Bucket 1")
    print(len(x))
    if(len(x) >= 1):
        worksheet.write(row, col+1, x[0])
    if(len(x) >= 2):
        worksheet.write(row, col+2, x[1])
    if(len(x) >= 3):
        worksheet.write(row, col+3, x[2])
    if(len(x) >= 4):
        worksheet.write(row, col+4, x[3])
    row+=1

row=1
for x in range(len(b1F)):
    worksheet.write_formula("F" + str(row+1), "=SUM(C" + str(row+1) + ":E" + str(row+1) + ")")
    row+=1

worksheet.write(row, 0, "Bucket 1", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write(row, 1, "All", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))

worksheet.write_formula("C" + str(row+1), "=SUM(C2:C" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("D" + str(row+1), "=SUM(D2:D" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("E" + str(row+1), "=SUM(E2:E" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("F" + str(row+1), "=SUM(F2:F" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))

row = row+1
for x in b2F:
	if(x[0] == "Ads" or x[0] == "FB"):
		worksheet.write(row, col, "Bucket 2")
		worksheet.write(row, col+1, " ")
		worksheet.write(row, col+2, " ")
		worksheet.write(row, col+3, " ")
		worksheet.write(row, col+4, " ")
		row+=1
	else:
		worksheet.write(row, col, "Bucket 2")
		worksheet.write(row, col+1, x[0])
		worksheet.write(row, col+2, x[1])
		worksheet.write(row, col+3, x[2])
		worksheet.write(row, col+4, x[3])
		row+=1

row = row-len(b2F)
for x in range(len(b2F)):
    worksheet.write_formula("F" + str(row+1), "=SUM(C" + str(row+1) + ":E" + str(row+1) + ")")
    row+=1

startIndex2 = row-len(b2F)+1
worksheet.write(row, 0, "Bucket 2", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write(row, 1, "All", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))

worksheet.write_formula("C" + str(row+1), "=SUM(C"+ str(startIndex2) + ":C" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("D" + str(row+1), "=SUM(D"+ str(startIndex2) + ":D" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("E" + str(row+1), "=SUM(E"+ str(startIndex2) + ":E" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("F" + str(row+1), "=SUM(F"+ str(startIndex2) + ":F" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))

row = row+1
for x in b3F:
	if(x[0] == "Ads" or x[0] == "FB"):
		worksheet.write(row, col, "Bucket 3")
		worksheet.write(row, col+1, " ")
		worksheet.write(row, col+2, " ")
		worksheet.write(row, col+3, " ")
		worksheet.write(row, col+4, " ")
		row+=1
	else:
		worksheet.write(row, col, "Bucket 3")
		worksheet.write(row, col+1, x[0])
		worksheet.write(row, col+2, x[1])
		worksheet.write(row, col+3, x[2])
		worksheet.write(row, col+4, x[3])
		row+=1

row = row-len(b3F)
for x in range(len(b3F)):
    worksheet.write_formula("F" + str(row+1), "=SUM(C" + str(row+1) + ":E" + str(row+1) + ")")
    row+=1

startIndex3 = row-len(b3F)+1
worksheet.write(row, 0, "Bucket 3", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write(row, 1, "All", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))

worksheet.write_formula("C" + str(row+1), "=SUM(C"+ str(startIndex3) + ":C" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("D" + str(row+1), "=SUM(D"+ str(startIndex3) + ":D" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("E" + str(row+1), "=SUM(E"+ str(startIndex3) + ":E" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))
worksheet.write_formula("F" + str(row+1), "=SUM(F"+ str(startIndex3) + ":F" + str(row) + ")", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))

worksheet.write(row+2, 0, "Total pumps shipped this month: ")
worksheet.write_formula(row+2, 1, str(totalShipped))

worksheet.write(row+4, 0, "This month app only shipments")
worksheet.write_formula(row+4, 1, "=K8")

worksheet.set_column("J:J", 27)
worksheet.set_column("M:M", 27)

worksheet.write("J2", "Total Apps (OB Only)")
worksheet.write_formula("K2", "=SUM(F8, F19)")
worksheet.write("J4", "Total Apps")
worksheet.write_formula("K4", "=SUM(F8,F19,F30)")
worksheet.write("J6", "OB Only, Shipped apps from this month apps", workbook.add_format({"text_wrap": 1}))
worksheet.write_formula("K6", "=SUM(D8, D19)")
worksheet.write("J8", "This month app only, Total Shipments", workbook.add_format({"text_wrap": 1}))
worksheet.write_formula("K8", "=SUM(D8, D19, D30)")
worksheet.write("J10", "Total Shipments", workbook.add_format({"bold": 1}))
worksheet.write_formula("K10", "=B32", workbook.add_format({"bold": 1}))
worksheet.write("J9", "Pumps shipped from backlog")
worksheet.write_formula("K9", "=K10-K8", workbook.add_format({"underline": 1}))
worksheet.write_formula("L9", "=K9/K10", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("J13", "Void Rate")
worksheet.write_formula("K13", "=(E8+E19+E30)/K4", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("J15", "Throughput Rate")
worksheet.write_formula("K15", "=K8/K4", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("J17", "Consumption Rate")
worksheet.write_formula("K17", "=K13+K15", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("J19", "Backlog Rate")
worksheet.write_formula("K19", "=1-K17", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("J21", "Gross Throughput Rate")
worksheet.write_formula("K21", "=K10/K4", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("J23", "Total Backlog")
worksheet.write_formula("K23", "=1-(K13+K21)", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("J25", "Total Backlog Pumps")
worksheet.write_formula("K25", "=K4*K19")

worksheet.write("G1", "Lead Source Percent\r\nof Bucket Apps", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white', 'text_wrap': 1}))
worksheet.set_column("G:G", 15)

worksheet.write("H1", "Lead Source Percent\r\nof Total Apps", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white', 'text_wrap': 1}))
worksheet.set_column("G:G", 15)

worksheet.write("I1", "Void Rate", workbook.add_format({'bold': True, 'bg_color': 'blue', 'font_color': 'white'}))

worksheet.write_formula("H8", "=E8/F8", workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))
worksheet.write_formula("H19", "=E19/F19", workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))
worksheet.write_formula("H30", "=E30/F30", workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))

worksheet.write("M2", "OB Throughput Rate:")
worksheet.write_formula("N2", "=K6/K2", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("M4", "OB Void Rate:")
worksheet.write_formula("N4", "=(E8+E19)/K2", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("M6", "Non-OB Void Rate:")
worksheet.write_formula("N6", "=E30/F30", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("M8", "OB Backlog:")
worksheet.write_formula("N8", "=1-(N2+N4)", workbook.add_format({"num_format": "0.00%"}))

worksheet.write("M10", "Total OB backlog pumps:")
worksheet.write_formula("N10", "=N8*K2")

for x in range(row+1):
    if(x==0):
        continue
    elif(x==7 or x==18 or x==29):
        worksheet.write_formula("H" + str(x+1), "=F" + str(x+1) + "/$K$4", workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))
    else:
        worksheet.write_formula("H" + str(x+1), "=F" + str(x+1) + "/$K$4", workbook.add_format({'num_format': '0.00%', 'bold': True}))

for x in range(row+1):
    if(x==0):
        continue
    elif(x==7 or x==18 or x==29):
        worksheet.write_formula("I" + str(x+1), "=E" + str(x+1) + "/F" + str(x+1), workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))
    else:
        worksheet.write_formula("I" + str(x+1), "=E" + str(x+1) + "/F" + str(x+1), workbook.add_format({'num_format': '0%', 'bold': True}))

for x in range(row+1):
    if(x==0):
        continue
    elif(x <= 7):
        worksheet.write_formula("G" + str(x+1), "=F" + str(x+1) + "/F8", workbook.add_format({'num_format': '0%', 'bold': True}))
        if(x==7):
            worksheet.write_formula("G" + str(x+1), "=F" + str(x+1) + "/F8", workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))
    elif(x >= 7 and x <= 18):
        worksheet.write_formula("G" + str(x+1), "=F" + str(x+1) + "/F19", workbook.add_format({'num_format': '0%', 'bold': True}))
        if(x==18):
            worksheet.write_formula("G" + str(x+1), "=F" + str(x+1) + "/F19", workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))
    elif(x >= 18 and x <= 29):
        worksheet.write_formula("G" + str(x+1), "=F" + str(x+1) + "/F30", workbook.add_format({'num_format': '0%', 'bold': True}))
        if(x==29):
            worksheet.write_formula("G" + str(x+1), "=F" + str(x+1) + "/F30", workbook.add_format({'bg_color': 'blue', 'num_format': '0%', 'font_color': 'white', 'bold': True}))
workbook.close()

cursor.close()
cnx.close()
