import mysql.connector
import xlsxwriter
import time

cnx = mysql.connector.connect(user="hgdwreadwrite", password="1vmiWAqndlX1",
                              host="34.232.48.193",
                              database="hygeiabtdw-v3")
cursor = cnx.cursor()

start = time.time()
print("Working...");

curMonthStart = 1
curMonthEnd = 2

for x in range(9):
    q1 = ("select distinct(OwnerObjKey) as SOKey from AuditTrail where ActivityType != 'Viewed' and AuditTrailType = 'Sales Order' and CreateDt >= '2020-0"+ str(curMonthStart) +"-01' and CreateDt <= '2020-0"+ str(curMonthEnd) +"-01' and MsgText like '%14%'");
    q2 = ("select distinct(OwnerObjKey) as SOKey from AuditTrail where ActivityType != 'Viewed' and AuditTrailType = 'Sales Order' and CreateDt >= '2020-0"+ str(curMonthStart) +"-01' and CreateDt <= '2020-0"+ str(curMonthEnd) +"-01' and MsgText like '%16%'");

    cursor.execute(q1)
    result = cursor.fetchall()

    SOKeys_W14 = "";
    SOKeys_W16 = "";
    wip14Count = 0
    wip16Count = 0

    for x in result:
        SOKeys_W14 += "'" + str(x[0]) + "', ";
        wip14Count += 1

    cursor.execute(q2)
    result2 = cursor.fetchall()

    for x in result2:
        SOKeys_W16 += "'" + str(x[0]) + "', ";
        wip16Count += 1


    q3 = ("SELECT COUNT(*) FROM SO WHERE SO.WIPStateKey = 56 AND SO.SOKey IN (" + SOKeys_W14[:-2] + ")");
    cursor.execute(q3)
    result1 = cursor.fetchall()

    state56apps_14 = 0;
    for x in result1:
        state56apps_14 = x[0]

    q4 = ("SELECT COUNT(*) FROM SO WHERE SO.WIPStateKey = 56 AND SO.SOKey IN (" + SOKeys_W16[:-2] + ")");
    cursor.execute(q4)
    result3 = cursor.fetchall()

    state56apps_16 = 0;
    for x in result3:
        state56apps_16 = x[0]

    print("2020-" + str(curMonthStart) + "-01 to 2020-" + str(curMonthEnd) + "-01")
    print("------------------------------------------------")
    print("WIP 14 TOTAL: " + str(wip14Count))
    print("WIP 51 Apps: " + str(state56apps_14))
    print("WIP 99 Apps: " + str(wip14Count-state56apps_14))
    print(" ")
    print("WIP 16 TOTAL: " + str(wip16Count))
    print("WIP 51 Apps: " + str(state56apps_16))
    print("WIP 99 Apps: " + str(wip16Count-state56apps_16))
    print("------------------------------------------------")
    print(" ")

    curMonthStart += 1
    curMonthEnd +=1

end = time.time()
print("Execution Time: (" + str(end-start) + "s)")
