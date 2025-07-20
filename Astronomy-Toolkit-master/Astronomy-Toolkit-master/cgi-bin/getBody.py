#!/usr/bin/env python
# -*- coding: UTF-8 -*-
import cgi
import cgitb
import json
from login import db


def main():
    cgitb.enable()
    cursor = db.cursor()
    cursor.execute("SELECT * FROM SmallBodies WHERE designation LIKE %s", "%" + get_search_querry() + "%")
    print "Content-type: text/html\n"
    results = cursor.fetchall()
    array = []
    for result in results:
        body = orbital_elements2dictionary(result[1], result[2], result[3], result[4], result[5], result[6], result[7])
        array.append(body)

    print json.dumps(array)


def orbital_elements2dictionary(name, semimajor_axis, eccentricty, inclination, longitude_of_node, longitude_of_pericenter, mean_anomaly2000):
    body = {
        'name' : name,
        'semiMajorAxis': semimajor_axis,
        'eccentricity' : eccentricty,
        'inclination' : inclination,
        'longitudeOfNode' : longitude_of_node,
        'longitudeOfPericenter' : longitude_of_pericenter,
        'meanAnomaly2000' : mean_anomaly2000
    }

    return body

def get_search_querry():
    form = cgi.FieldStorage()
    return form.getvalue('name')



if __name__ == "__main__":
    main()
