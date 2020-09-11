import mysql.connector
from datetime import datetime, timedelta
import xlsxwriter

thisMonth = datetime.now()
nextMonth = datetime.now() + timedelta(days=30)
yesterday = datetime.now() - timedelta(days=3)

startDate = thisMonth.strftime("%Y-%m-01")
endDate = nextMonth.strftime("%Y-%m-01")

startDateD = yesterday.strftime("%Y-%m-%d")

cnx = mysql.connector.connect(user="#########", password="##########",
                              host="#############",
                              database="############")
cursor = cnx.cursor()

print("Working...")
## US #####################

q1 = ("select distinct(SOKey) from Invoice join InvoiceDtl using (InvKey) where ServiceDt > '"+ startDate +"' and ServiceDt < '"+ endDate +"' and InvoiceDtl.ProcCode = 'E0603';")
cursor.execute(q1)
result = cursor.fetchall()


humanOrders = {}
for x in result:
        humanOrders[str(x[0])] = 0


q2 = ("select distinct(EmailAddr),OwnerObjKey from AuditTrail join Secuser on (AuditTrail.ChangedBy = Secuser.SecUserKey) join SO on(OwnerObjKey=SOKey) where AuditTrailType = 'Sales Order' and ActivityType = 'Saved' and EmailAddr is not null and EmailAddr not in ('todd@moneypath.com','robert.bullock@gmail.com','rbullock@hygeiababy.com') and substring(MsgText,0,0) != 'ConfirmDt' and CreateDt < SOConfirmDt and OwnerObjKey in (" + ",".join(humanOrders.keys()) + ")")
cursor.execute(q2)
result2 = cursor.fetchall()

for x in result2:
    humanOrders[str(x[1])] = 1

human = 0;
nonHuman = 0;

for x, y in humanOrders.items():
    if(y):
        human+=1
    else:
        nonHuman+=1

## CA #####################

q3 = ("select distinct(SOKey) from Invoice join InvoiceDtl using (InvKey) join SO using (SOKey) where DeliveryAddrState = 'CA' and ServiceDt > '"+ startDate +"' and ServiceDt < '"+ endDate +"' and InvoiceDtl.ProcCode = 'E0603';")
cursor.execute(q3)
result3 = cursor.fetchall()


CAOrders = {}
for x in result3:
        CAOrders[str(x[0])] = 0


q4 = ("select distinct(EmailAddr),OwnerObjKey from AuditTrail join Secuser on (AuditTrail.ChangedBy = Secuser.SecUserKey) join SO on(OwnerObjKey=SOKey) where DeliveryAddrState = 'CA' and AuditTrailType = 'Sales Order' and ActivityType = 'Saved' and EmailAddr is not null and EmailAddr not in ('todd@moneypath.com','robert.bullock@gmail.com','rbullock@hygeiababy.com') and substring(MsgText,0,0) != 'ConfirmDt' and CreateDt < SOConfirmDt and OwnerObjKey in (" + ",".join(humanOrders.keys()) + ")")
cursor.execute(q4)
result4 = cursor.fetchall()

for x in result4:
    CAOrders[str(x[1])] = 1

humanCA = 0;
nonHumanCA = 0;

for x, y in CAOrders.items():
    if(y):
        humanCA+=1
    else:
        nonHumanCA+=1

## TX #####################

q5 = ("select distinct(SOKey) from Invoice join InvoiceDtl using (InvKey) join SO using (SOKey) where DeliveryAddrState = 'TX' and ServiceDt > '"+ startDate +"' and ServiceDt < '"+ endDate +"' and InvoiceDtl.ProcCode = 'E0603';")
cursor.execute(q5)
result5 = cursor.fetchall()


TXOrders = {}
for x in result5:
        TXOrders[str(x[0])] = 0


q6 = ("select distinct(EmailAddr),OwnerObjKey from AuditTrail join Secuser on (AuditTrail.ChangedBy = Secuser.SecUserKey) join SO on(OwnerObjKey=SOKey) where DeliveryAddrState = 'CA' and AuditTrailType = 'Sales Order' and ActivityType = 'Saved' and EmailAddr is not null and EmailAddr not in ('todd@moneypath.com','robert.bullock@gmail.com','rbullock@hygeiababy.com') and substring(MsgText,0,0) != 'ConfirmDt' and CreateDt < SOConfirmDt and OwnerObjKey in (" + ",".join(humanOrders.keys()) + ")")
cursor.execute(q6)
result6 = cursor.fetchall()

for x in result6:
    TXOrders[str(x[1])] = 1

humanTX = 0;
nonHumanTX = 0;

for x, y in TXOrders.items():
    if(y):
        humanTX+=1
    else:
        nonHumanTX+=1

################## DAILYS ###########################

## US #####################

q7 = ("select distinct(SOKey) from Invoice join InvoiceDtl using (InvKey) where ServiceDt >= '"+ startDateD +"' and InvoiceDtl.ProcCode = 'E0603';")
cursor.execute(q7)
result7 = cursor.fetchall()


humanOrdersD = {}
for x in result7:
        humanOrdersD[str(x[0])] = 0


q8 = ("select distinct(EmailAddr),OwnerObjKey from AuditTrail join Secuser on (AuditTrail.ChangedBy = Secuser.SecUserKey) join SO on(OwnerObjKey=SOKey) where AuditTrailType = 'Sales Order' and ActivityType = 'Saved' and EmailAddr is not null and EmailAddr not in ('todd@moneypath.com','robert.bullock@gmail.com','rbullock@hygeiababy.com') and substring(MsgText,0,0) != 'ConfirmDt' and CreateDt < SOConfirmDt and OwnerObjKey in (" + ",".join(humanOrdersD.keys()) + ")")
cursor.execute(q8)
result8 = cursor.fetchall()

for x in result8:
    humanOrdersD[str(x[1])] = 1

humanD = 0;
nonHumanD = 0;

for x, y in humanOrdersD.items():
    if(y):
        humanD+=1
    else:
        nonHumanD+=1

## CA #####################

q9 = ("select distinct(SOKey) from Invoice join InvoiceDtl using (InvKey) join SO using (SOKey) where DeliveryAddrState = 'CA' and ServiceDt >= '"+ startDateD +"' and InvoiceDtl.ProcCode = 'E0603';")
cursor.execute(q9)
result9 = cursor.fetchall()


CAOrdersD = {}
for x in result9:
        CAOrdersD[str(x[0])] = 0


q10 = ("select distinct(EmailAddr),OwnerObjKey from AuditTrail join Secuser on (AuditTrail.ChangedBy = Secuser.SecUserKey) join SO on(OwnerObjKey=SOKey) where DeliveryAddrState = 'CA' and AuditTrailType = 'Sales Order' and ActivityType = 'Saved' and EmailAddr is not null and EmailAddr not in ('todd@moneypath.com','robert.bullock@gmail.com','rbullock@hygeiababy.com') and substring(MsgText,0,0) != 'ConfirmDt' and CreateDt < SOConfirmDt and OwnerObjKey in (" + ",".join(humanOrdersD.keys()) + ")")
cursor.execute(q10)
result10 = cursor.fetchall()

for x in result10:
    CAOrdersD[str(x[1])] = 1

humanCAD = 0;
nonHumanCAD = 0;

for x, y in CAOrdersD.items():
    if(y):
        humanCAD+=1
    else:
        nonHumanCAD+=1

## TX #####################

q11 = ("select distinct(SOKey) from Invoice join InvoiceDtl using (InvKey) join SO using (SOKey) where DeliveryAddrState = 'TX' and ServiceDt >= '"+ startDateD +"' and InvoiceDtl.ProcCode = 'E0603';")
cursor.execute(q11)
result11 = cursor.fetchall()


TXOrdersD = {}
for x in result11:
        TXOrdersD[str(x[0])] = 0


q12 = ("select distinct(EmailAddr),OwnerObjKey from AuditTrail join Secuser on (AuditTrail.ChangedBy = Secuser.SecUserKey) join SO on(OwnerObjKey=SOKey) where DeliveryAddrState = 'TX' and AuditTrailType = 'Sales Order' and ActivityType = 'Saved' and EmailAddr is not null and EmailAddr not in ('todd@moneypath.com','robert.bullock@gmail.com','rbullock@hygeiababy.com') and substring(MsgText,0,0) != 'ConfirmDt' and CreateDt < SOConfirmDt and OwnerObjKey in (" + ",".join(humanOrdersD.keys()) + ")")
cursor.execute(q12)
result12 = cursor.fetchall()

for x in result12:
    TXOrdersD[str(x[1])] = 1

humanTXD = 0;
nonHumanTXD = 0;

for x, y in TXOrdersD.items():
    if(y):
        humanTXD+=1
    else:
        nonHumanTXD+=1


wb = xlsxwriter.Workbook('C:/Users/hygadmin/Desktop/Hygeia Daily Report/reports/Intake/Intake Report ' + thisMonth.strftime("%Y-%m-%d") + '.xlsx')
ws = wb.add_worksheet()

## US

ws.write("A1", "Pump Only, Total Shipments, All States MTD", wb.add_format({'text_wrap':1, 'bg_color':'blue', 'color':'white', 'bold':1}))
ws.set_column(0, 0, 35)

ws.write("B1", human+nonHuman, wb.add_format({'text_wrap':1, 'bg_color':'blue', 'color':'white', 'align':'center'}))
ws.write("C1", "% of Total", wb.add_format({'text_wrap':1, 'bg_color':'blue', 'color':'white', 'align':'center'}))

ws.write("A3", "Total Human Processed, all states", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("B3", human, wb.add_format({'align':'center'}))
ws.write("A4", "Total Automation Processed, all states", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("B4", nonHuman, wb.add_format({'align':'center'}))

ws.write_formula("C3", "=B3/B1", wb.add_format({'num_format':'0.00%'}))
ws.write_formula("C4", "=B4/B1", wb.add_format({'num_format':'0.00%'}))

## CA

ws.write("A6", "Pump Only, Total Shipments, CA Only MTD", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'bold':1}))

ws.write("B6", humanCA+nonHumanCA, wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))
ws.write("C6", "% of Total", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))

ws.write("A8", "Total Human Processed, CA only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("B8", humanCA, wb.add_format({'align':'center'}))
ws.write("A9", "Total Automation Processed, CA only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("B9", nonHumanCA, wb.add_format({'align':'center'}))

ws.write_formula("C8", "=B8/B6", wb.add_format({'num_format':'0.00%'}))
ws.write_formula("C9", "=B9/B6", wb.add_format({'num_format':'0.00%'}))

## TX

ws.write("A11", "Pump Only, Total Shipments, TX Only MTD", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'bold':1}))

ws.write("B11", humanTX+nonHumanTX, wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))
ws.write("C11", "% of Total", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))

ws.write("A13", "Total Human Processed, TX only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("B13", humanTX, wb.add_format({'align':'center'}))
ws.write("A14", "Total Automation Processed, TX only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("B14", nonHumanTX, wb.add_format({'align':'center'}))

ws.write_formula("C13", "=B13/B11", wb.add_format({'num_format':'0.00%'}))
ws.write_formula("C14", "=B14/B11", wb.add_format({'num_format':'0.00%'}))


#################### DAILYS #######################

ws.write("E1", "Pump Only, Total Shipments, All States Daily", wb.add_format({'text_wrap':1, 'bg_color':'blue', 'color':'white', 'bold':1}))
ws.set_column(4, 4, 35)


ws.write("F1", humanD+nonHumanD, wb.add_format({'text_wrap':1, 'bg_color':'blue', 'color':'white', 'align':'center'}))
ws.write("G1", "% of Total", wb.add_format({'text_wrap':1, 'bg_color':'blue', 'color':'white', 'align':'center'}))

ws.write("E3", "Total Human Processed, all states", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("F3", humanD, wb.add_format({'align':'center'}))
ws.write("E4", "Total Automation Processed, all states", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("F4", nonHumanD, wb.add_format({'align':'center'}))

ws.write_formula("G3", "=F3/F1", wb.add_format({'num_format':'0.00%'}))
ws.write_formula("G4", "=F4/F1", wb.add_format({'num_format':'0.00%'}))

## CA

ws.write("E6", "Pump Only, Total Shipments, CA Only Daily", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'bold':1}))

ws.write("F6", humanCAD+nonHumanCAD, wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))
ws.write("G6", "% of Total", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))

ws.write("E8", "Total Human Processed, CA only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("F8", humanCAD, wb.add_format({'align':'center'}))
ws.write("E9", "Total Automation Processed, CA only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("F9", nonHumanCAD, wb.add_format({'align':'center'}))

ws.write_formula("G8", "=F8/F6", wb.add_format({'num_format':'0.00%'}))
ws.write_formula("G9", "=F9/F6", wb.add_format({'num_format':'0.00%'}))

## TX

ws.write("E11", "Pump Only, Total Shipments, TX Only Daily", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'bold':1}))

ws.write("F11", humanTXD+nonHumanTXD, wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))
ws.write("G11", "% of Total", wb.add_format({'text_wrap':1, 'bg_color':'#FADA5E', 'color':'white', 'align':'center'}))

ws.write("E13", "Total Human Processed, TX only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("F13", humanTXD, wb.add_format({'align':'center'}))
ws.write("E14", "Total Automation Processed, TX only", wb.add_format({'bold':1, 'text_wrap':1}))
ws.write("F14", nonHumanTXD, wb.add_format({'align':'center'}))

ws.write_formula("G13", "=F13/F11", wb.add_format({'num_format':'0.00%'}))
ws.write_formula("G14", "=F14/F11", wb.add_format({'num_format':'0.00%'}))

wb.close()
print("Report Generated.")
