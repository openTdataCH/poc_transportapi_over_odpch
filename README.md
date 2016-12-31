# Transport #

Swiss public transport API over ODPCH

Matthias Günter, 2016

## API Documentation ##

The original documentation can be found here: [https://transport.opendata.ch/docs.html](https://transport.opendata.ch/docs.html)

The ODPCH documentation can be found here (in German):
[https://opentransportdata.swiss/de/cookbook/](https://opentransportdata.swiss/de/cookbook/)

The documentation here only points out the differences to the original TransportAPI implementation.
 
### Rate Limiting ###
The current implementation uses a standard API-key and is therefore limited. If you want more capacity. Download the source and get your own key.

### Resources ###

#### /locations ####

> http://transport.actinvoice.com/locations

Request Parameters:

- type: not supported
- transportations: not supported
- limits: handles limits differently. Not according to time, but really n results.

Response Parameters:

- Stations don't support score. Distance is only supported, when searched by geo coordinates.

##### Example requests #####
    
    GET http://transport.actinvoice.com/locations?query=Basel
    GET http://transport.actinvoice.com/locations?x=8.061&y=47.451

##### Example response #####

    {
	"stations": [
		{
			"id": "8500591",
			"name": "Asp AG, Abzw.",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.053752",
				"y": "47.446092"
			},
			"distance": 970.32290672028
		},
		{
			"id": "8500592",
			"name": "K\u00fcttigen, Abzw. Giebel",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.052109",
				"y": "47.421977"
			},
			"distance": 3344.8039043624
		},
		{
			"id": "8500687",
			"name": "K\u00fcttigen, Benken-Klus",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.037596",
				"y": "47.423079"
			},
			"distance": 4027.7193214543
		},
		{
			"id": "8500976",
			"name": "Zeihen, Hohb\u00e4chli",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.085483",
				"y": "47.476850"
			},
			"distance": 3938.342182614
		},
		{
			"id": "8500977",
			"name": "Densb\u00fcren, Gemeindehaus",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.051929",
				"y": "47.455851"
			},
			"distance": 1141.3227807244
		},
		{
			"id": "8500978",
			"name": "Herznach, Post",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.050542",
				"y": "47.474782"
			},
			"distance": 2864.9608612133
		},
		{
			"id": "8502682",
			"name": "K\u00fcttigen, Grossmatt",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.053026",
				"y": "47.429141"
			},
			"distance": 2564.7590330315
		},
		{
			"id": "8502687",
			"name": "K\u00fcttigen, Fischbach",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.034624",
				"y": "47.429930"
			},
			"distance": 3739.4194545716
		},
		{
			"id": "8502893",
			"name": "Staffelegg, Passh\u00f6he",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.060317",
				"y": "47.433779"
			},
			"distance": 1897.4895337732
		},
		{
			"id": "8572391",
			"name": "Densb\u00fcren, Ausserdorf",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.053155",
				"y": "47.452939"
			},
			"distance": 898.06606721082
		},
		{
			"id": "8572392",
			"name": "Densb\u00fcren, Breite",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.048531",
				"y": "47.462597"
			},
			"distance": 1884.830603901
		},
		{
			"id": "8572393",
			"name": "Herznach, Oberherznach",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.053897",
				"y": "47.470151"
			},
			"distance": 2251.5472066039
		},
		{
			"id": "8572394",
			"name": "Ueken, Zeihen Abzw.",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.052465",
				"y": "47.480519"
			},
			"distance": 3385.7005831066
		},
		{
			"id": "8572459",
			"name": "Zeihen, Stauftel",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.088275",
				"y": "47.478444"
			},
			"distance": 4280.9884368639
		},
		{
			"id": "8572460",
			"name": "Zeihen, Dorf",
			"score": null,
			"coordinate": {
				"type": "WGS84",
				"x": "8.083445",
				"y": "47.476232"
			},
			"distance": 3734.3591001069
		}
	]
    }
#### /connections ####

    http://transport.actinvoice.com/connections

Request Parameters:

- via: not supported
- transportations: not supported
- page: not supported
- direct: is always 1
- sleeper: not supported
- couchette: not supported
- bike: not supported
- accessibility: not supported

Response Parameters:

- Connections don't support duration, products, capacity1st, capacity2nd, sections

##### Example request #####

    GET http://transport.actinvoice.com/connections?from=8507000&to=8503000

    GET http://transport.actinvoice.com/connections?from=8507000&to=8503000&date=2016-12-12&time=20:00&isArrivalTime=1&limit=8
        
##### Example response #####

    {
	"connections": [
		{
			"from": {
				"station": {
					"id": "8507000",
					"name": "Bern$<1>$Berna$<4>$Berne (CH)$<4>$BN$<3>",
					"score": null,
					"coordinate": {
						"type": "WGS84",
						"x": "7.439118",
						"y": "46.948825"
					},
					"distance": null
				},
				"arrival": null,
				"departure": "2016-12-31T14:34:00Z",
				"platform": null,
				"prognosis": {
					"platform": null,
					"departure": "2016-12-31T14:36:00Z",
					"arrival": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				}
			},
			"to": {
				"station": {
					"id": "8503000",
					"name": "Z\u00fcrich HB$<1>$ZH$<4>$Zurich$<4>$Zurigo$<4>$Z\u00fcrich$<4>$ZUE$<3>",
					"score": null,
					"coordinate": {
						"type": "WGS84",
						"x": "8.540192",
						"y": "47.378177"
					},
					"distance": null
				},
				"arrival": "2016-12-31T15:54:00Z",
				"departure": null,
				"platform": null,
				"prognosis": {
					"platform": null,
					"departure": null,
					"arrival": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				}
			},
			"duration": "78",
			"products": [
				"rail"
			],
			"capacity1st": "-1",
			"capacity2nd": "-1"
		},
		{
			"from": {
				"station": {
					"id": "8507000",
					"name": "Bern$<1>$Berna$<4>$Berne (CH)$<4>$BN$<3>",
					"score": null,
					"coordinate": {
						"type": "WGS84",
						"x": "7.439118",
						"y": "46.948825"
					},
					"distance": null
				},
				"arrival": null,
				"departure": "2016-12-31T15:02:00Z",
				"platform": null,
				"prognosis": {
					"platform": null,
					"departure": "2016-12-31T15:02:00Z",
					"arrival": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				}
			},
			"to": {
				"station": {
					"id": "8503000",
					"name": "Z\u00fcrich HB$<1>$ZH$<4>$Zurich$<4>$Zurigo$<4>$Z\u00fcrich$<4>$ZUE$<3>",
					"score": null,
					"coordinate": {
						"type": "WGS84",
						"x": "8.540192",
						"y": "47.378177"
					},
					"distance": null
				},
				"arrival": "2016-12-31T15:58:00Z",
				"departure": null,
				"platform": null,
				"prognosis": {
					"platform": null,
					"departure": null,
					"arrival": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				}
			},
			"duration": "56",
			"products": [
				"rail"
			],
			"capacity1st": "-1",
			"capacity2nd": "-1"
		},
		{
			"from": {
				"station": {
					"id": "8507000",
					"name": "Bern$<1>$Berna$<4>$Berne (CH)$<4>$BN$<3>",
					"score": null,
					"coordinate": {
						"type": "WGS84",
						"x": "7.439118",
						"y": "46.948825"
					},
					"distance": null
				},
				"arrival": null,
				"departure": "2016-12-31T15:32:00Z",
				"platform": null,
				"prognosis": {
					"platform": null,
					"departure": "2016-12-31T15:32:00Z",
					"arrival": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				}
			},
			"to": {
				"station": {
					"id": "8503000",
					"name": "Z\u00fcrich HB$<1>$ZH$<4>$Zurich$<4>$Zurigo$<4>$Z\u00fcrich$<4>$ZUE$<3>",
					"score": null,
					"coordinate": {
						"type": "WGS84",
						"x": "8.540192",
						"y": "47.378177"
					},
					"distance": null
				},
				"arrival": "2016-12-31T16:28:00Z",
				"departure": null,
				"platform": null,
				"prognosis": {
					"platform": null,
					"departure": null,
					"arrival": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				}
			},
			"duration": "56",
			"products": [
				"rail"
			],
			"capacity1st": "-1",
			"capacity2nd": "-1"
		}
	]
    }

#### /stationboard ####

    http://transport.actinvoice.com/stationboard
    
Request Parameters:

- station: Be aware, that ODPCH does not use importance of station. Therefore you can get anything. E.g. Zürich will not get you Zürich HB.
- transportations: not supported

Response Parameters:

- station: ODPCH does not distinguish if it is a station. So you can get an operating point that does you no good.
- stationboard

##### Example request #####

    GET http://transport.actinvoice.com/stationboard?id=8507000
    GET http://transport.actinvoice.com/stationboard?station=Wankdorf
    GET http://transport.actinvoice.com/stationboard?id=8503000&datetime=2016-12-12T22:00:00&type=arrival
    GET http://transport.actinvoice.com/stationboard?id=8503000&datetime=2016-12-12T22:00:00&type=departure
    GET http://transport.actinvoice.com/stationboard?id=8503000&datetime=2016-12-12T22:00:00&type=arrival&limit=3
    
    
##### Example response #####

    {
	"stationboard": [
		{
			"stop": {
				"station": [
					{
						"id": "8503000",
						"name": "Z\u00fcrich HB$<1>$ZH$<4>$Zurich$<4>$Zurigo$<4>$Z\u00fcrich$<4>$ZUE$<3>",
						"score": null,
						"coordinate": {
							"type": "WGS84",
							"x": "8.540192",
							"y": "47.378177"
						},
						"distance": null
					}
				],
				"arrival": "2016-12-12T21:00:00Z",
				"arrivalTimeStamp": null,
				"departure": null,
				"departureTimestamp": null,
				"platform": "",
				"prognosis": {
					"platform": "",
					"arrival": null,
					"departure": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				},
				"name": "odp:26003::H:j17:18385",
				"category": "S-Bahn",
				"number": "3",
				"operator": "odp:11",
				"to": "Wetzikon"
			}
		},
		{
			"stop": {
				"station": [
					{
						"id": "8503000",
						"name": "Z\u00fcrich HB$<1>$ZH$<4>$Zurich$<4>$Zurigo$<4>$Z\u00fcrich$<4>$ZUE$<3>",
						"score": null,
						"coordinate": {
							"type": "WGS84",
							"x": "8.540192",
							"y": "47.378177"
						},
						"distance": null
					}
				],
				"arrival": "2016-12-12T21:00:00Z",
				"arrivalTimeStamp": null,
				"departure": null,
				"departureTimestamp": null,
				"platform": "",
				"prognosis": {
					"platform": "",
					"arrival": null,
					"departure": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				},
				"name": "odp:54003:Y:H:j17:79",
				"category": "ICE",
				"number": [
				],
				"operator": "odp:11",
				"to": "Z\u00fcrich HB"
			}
		},
		{
			"stop": {
				"station": [
					{
						"id": "8503000",
						"name": "Z\u00fcrich HB$<1>$ZH$<4>$Zurich$<4>$Zurigo$<4>$Z\u00fcrich$<4>$ZUE$<3>",
						"score": null,
						"coordinate": {
							"type": "WGS84",
							"x": "8.540192",
							"y": "47.378177"
						},
						"distance": null
					}
				],
				"arrival": "2016-12-12T21:03:00Z",
				"arrivalTimeStamp": null,
				"departure": null,
				"departureTimestamp": null,
				"platform": "",
				"prognosis": {
					"platform": "",
					"arrival": null,
					"departure": null,
					"capacity1st": "-1",
					"capacity2nd": "-1"
				},
				"name": "odp:26009:A:H:j17:18984",
				"category": "S-Bahn",
				"number": "9",
				"operator": "odp:11",
				"to": "Schaffhausen"
			}
		}
	]
    }
### API Objects ###

#### Location Object####

- type:not supported, set to station (but is not always true
- score: not supported, set to 0
- distance: not supported, set to 0

#### Coordinates Object ####

- type is always 'WGS84'

#### Connection Object ####

- service: not supported
- products: not supported (empty)
- capacity1st: not supported (set to 1)
- capacity2nd: not supported (set to 1)
- sections: not supported

#### Checkpoint Object ####

- time always as ZULU time

#### Service Object ####

object not supported

#### Prognosis Object ####

- platform currently not supported (coming soon)
- dates are in ZULU time
- capacity1st: not supported (set to 1)
- capacity2nd: not supported (set to 1)

#### Stop Object ####

- name: the journeyref from VDV 431
- category: contains the submode from VDV 431
- number: the line number


#### Section Object ####

Object not supported

#### Journey Object ####
Minimalistic support for station board

- passList: not supported
- capacity1st: not supported (set to 1)
- capacity2nd: not supported (set to 1) 

## Source ##

https://github.com/openTdataCH/poc_transportapi_over_odpch
