# Transport #

Swiss public transport API over ODPCH

Matthias Günter, 2016

Updated by Maki Mikkelson

## API Documentation ##

The original documentation can be found here: [https://transport.opendata.ch/docs.html](https://transport.opendata.ch/docs.html)

The ODPCH documentation can be found here (in German):
[https://opentransportdata.swiss/de/cookbook/](https://opentransportdata.swiss/de/cookbook/)

The documentation here only points out the differences to the original TransportAPI implementation.
 
### Rate Limiting ###
The current implementation uses a standard API-key and is therefore limited. It also is only a PoC implementation. If you want more capacity, download the source, install it yourself, run it and get your own key.

### Resources ###

#### /locations ####

> http://transport.gnostx.com/locations

Request Parameters:

- query: specifies the location name to search for (e.g. "Basel" or "8507000")
- lat: Latitude (e.g. 47.476001)
- long: Longitude (e.g. 8.306130)

Important:

- if you are looking for a BPUIC
- type: not supported
- transportations: not supported
- limits: handles limits differently. Not according to time, but really n results.

Response Parameters:

- list of locations 
- Stations don't support score. Distance is only supported, when searched by geo coordinates.
- lat, long search returns all in 0.03 distance

##### Example requests #####
    
    GET http://transport.gnostx.com/locations?query=Basel
    GET http://transport.gnostx.com/locations?lat=47.451&long=8.061

##### Example response #####

```
{
	"stations": [
		{
			"id": "8586887",
			"sloid": "ch:1:sloid:86887",
			"name": "28 PU  P. Glanzmann Zeihen",
			"coordinate": {
				"type": "WGS84",
				"latitude": "47.47303319802",
				"longitude": "8.08315157092",
				"IERS": "447.8"
			},
			"distance": 5740984.154887024
		},
		{
			"id": "8500977",
			"sloid": "ch:1:sloid:977",
			"name": "Densb\u00fcren, Gemeindehaus",
			"coordinate": {
				"type": "WGS84",
				"latitude": "47.45585105687",
				"longitude": "8.05192859512",
				"IERS": "469"
			},
			"distance": 5741721.736311883
		},
		{
			"id": "8500977",
			"sloid": "ch:1:sloid:977",
			"name": "Densb\u00fcren, Gemeindehaus",
			"coordinate": {
				"type": "WGS84",
				"latitude": "47.45585105687",
				"longitude": "8.05192859512",
				"IERS": "469"
			},
			"distance": 5741721.736311883
		},
		{
			"id": "8500687",
			"sloid": "ch:1:sloid:687",
			"name": "K\u00fcttigen, Benken-Klus",
			"coordinate": {
				"type": "WGS84",
				"latitude": "47.42307877442",
				"longitude": "8.03759598776",
				"IERS": "462"
			},
			"distance": 5740407.65346529
		}
	]
}
```

#### /connections ####

    http://transport.gnostx.com/connections

Request Parameters:

- from (required): Specifies the departure location of the connection (e.g. "Lausanne")
- to (required): Specifies the arrival location of the connection (e.g. "Genève")
  - BPUIC as from/to parameter should be prefered, since location look ups is taking the first entry, if there are many
- date: Date of the connection, in the format YYYY-MM-DD (e.g. 2017-01-06)
- time: Time of the connection, in the format hh:mm (e.g. 17:30)
- isArrivalTime: defaults to 0, if set to 1 the passed date and time is the arrival time (e.g. 1)
- limit: 1-6. Specifies the number of connections to return. If several connections depart at the same time they are counted as 1 according to transportapi specification, but not here (e.g. 1)

Important:

- via: not supported
- transportations: not supported
- page: not supported
- direct: is always 1
- sleeper: not supported
- couchette: not supported
- bike: not supported
- accessibility: not supported

Response Parameters:

- Connections a list of connections
- Connections don't support duration, products, capacity1st, capacity2nd, sections

##### Example request #####

    GET http://transport.gnostx.com/connections?from=8507000&to=8503000
    GET http://transport.gnostx.com/connections?from=8507000&to=8503000&date=2016-12-12&time=20:00&isArrivalTime=1&limit=8
        
##### Example response #####

```
"connections": {
  "results": {
    "trip-context": {
      "situations": {
        "pt-situations": [
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": [
              {
                "source-type": "other"
              }
            ],
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Links"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "1": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Links"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "2": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Rechts"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "3": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Links"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "4": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Links"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "5": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Links"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "6": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Rechts"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "7": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Links"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "8": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Rechts"
          },
          {
            "creation-time": "2023-04-19T17:38:21Z",
            "version": "-1",
            "source": {
              "9": {
                "source-type": "other"
              }
            },
            "unknown-reason": "unknown",
            "priority": "-1",
            "summary": "Aussteigeseite: Links"
          }
        ]
      }
    },
    "trip-result": [
      {
        "result-id": "ID-5405FAB0-4CD3-4B07-B39F-CE3F0D90F0FA",
        "trip": [
          {
            "trip-id": "ID-E4DE787D-F77E-47D6-8388-0036615DD3B3",
            "duration": "PT1H19M",
            "start-time": "2023-04-19T17:37:00Z",
            "end-time": "2023-04-19T18:56:00Z",
            "inter-changes": "1",
            "distance": "118214",
            "trip-leg": [
              {
                "leg-id": "1",
                "timed-leg": [
                  {
                    "leg-board": [
                      {
                        "stop-point-reference": "8507000",
                        "stop-name": [
                          {
                            "text": [
                              "Bern"
                            ],
                            "language": "de"
                          }
                        ],
                        "planned-track": [
                          {
                            "text": {
                              "1": "4"
                            },
                            "language": "de"
                          }
                        ],
                        "departure": [
                          {
                            "date-time": "2023-04-19T17:36:00Z",
                            "estimated-time": "2023-04-19T17:37:00Z"
                          }
                        ],
                        "stops": "1"
                      }
                    ],
                    "legalight": [
                      {
                        "stop-point-reference": "8500218",
                        "stop-name": {
                          "1": {
                            "text": {
                              "2": "Olten"
                            },
                            "language": "de"
                          }
                        },
                        "planned-track": {
                          "1": {
                            "text": {
                              "3": "7"
                            },
                            "language": "de"
                          }
                        },
                        "service-arrival": [
                          {
                            "date-time": "2023-04-19T18:03:00Z",
                            "estimated-time": "2023-04-19T18:03:00Z"
                          }
                        ],
                        "stops": "2"
                      }
                    ],
                    "service": [
                      {
                        "operating-date": "2023-04-19",
                        "journey-reference": "ojp:91061:A:H:j23:226:1082",
                        "line-reference": "ojp:91061:A:H",
                        "direction": "outward",
                        "mode": [
                          {
                            "pt-mode": "rail",
                            "sub-mode": "interregionalRail",
                            "name": [
                              {
                                "text": "Zug",
                                "language": "de"
                              }
                            ]
                          }
                        ],
                        "published-line-name": [
                          {
                            "text": {
                              "4": "IC61"
                            },
                            "language": "de"
                          }
                        ],
                        "operation-reference": "ojp:11",
                        "attribute": [
                          {
                            "text": [
                              {
                                "text": "Businesszone in 1. Klasse",
                                "language": "de"
                              }
                            ],
                            "code": "A__BZ"
                          },
                          {
                            "text": {
                              "1": {
                                "text": "Ruhezone in 1. Klasse",
                                "language": "de"
                              }
                            },
                            "code": "A__RZ"
                          },
                          {
                            "text": {
                              "2": {
                                "text": "Gratis-Internet mit der App SBB FreeSurf",
                                "language": "de"
                              }
                            },
                            "code": "A__FS"
                          },
                          {
                            "text": {
                              "3": {
                                "text": "Restaurant",
                                "language": "de"
                              }
                            },
                            "code": "A__WR"
                          },
                          {
                            "text": {
                              "4": {
                                "text": "Platzreservierung m\u00f6glich",
                                "language": "de"
                              }
                            },
                            "code": "A___R"
                          },
                          {
                            "text": {
                              "5": {
                                "text": "Familienwagen mit Spielplatz",
                                "language": "de"
                              }
                            },
                            "code": "A__FA"
                          },
                          {
                            "text": {
                              "6": {
                                "text": "Aussteigeseite: Links",
                                "language": "de"
                              }
                            },
                            "code": "ojp91061AH_InfoCall226_111055_1"
                          }
                        ],
                        "origin-text": [
                          {
                            "language": "de"
                          }
                        ],
                        "destination-stop-reference": "8500010",
                        "destination-text": [
                          {
                            "text": {
                              "13": "Basel SBB"
                            },
                            "language": "de"
                          }
                        ]
                      }
                    ],
                    "track": [
                      {
                        "track-section": [
                          {
                            "track-start": [
                              {
                                "stop-point-reference": "8507000",
                                "location-name": [
                                  {
                                    "text": "Bern",
                                    "language": "de"
                                  }
                                ]
                              }
                            ],
                            "track-end": [
                              {
                                "stop-point-reference": "8500218",
                                "location-name": {
                                  "1": {
                                    "text": "Olten",
                                    "language": "de"
                                  }
                                }
                              }
                            ],
                            "duration": "PT27M",
                            "length": "63692"
                          }
                        ]
                      }
                    ]
                  }
                ]
              },
              {
                "leg-id": "2",
                "timed-leg": {
                  "1": {
                    "leg-board": {
                      "1": {
                        "stop-point-reference": "8500218",
                        "stop-name": {
                          "2": {
                            "text": {
                              "14": "Olten"
                            },
                            "language": "de"
                          }
                        },
                        "planned-track": {
                          "2": {
                            "text": {
                              "15": "7"
                            },
                            "language": "de"
                          }
                        },
                        "departure": {
                          "1": {
                            "date-time": "2023-04-19T18:20:00Z",
                            "estimated-time": "2023-04-19T18:20:00Z"
                          }
                        },
                        "stops": "1"
                      }
                    },
                    "intermetiates": [
                      {
                        "stop-point-reference": "8502113",
                        "stop-name": {
                          "3": {
                            "text": {
                              "16": "Aarau"
                            },
                            "language": "de"
                          }
                        },
                        "planned-track": {
                          "3": {
                            "text": {
                              "17": "3"
                            },
                            "language": "de"
                          }
                        },
                        "service-arrival": {
                          "1": {
                            "date-time": "2023-04-19T18:29:00Z",
                            "estimated-time": "2023-04-19T18:29:00Z"
                          }
                        },
                        "departure": {
                          "2": {
                            "date-time": "2023-04-19T18:31:00Z",
                            "estimated-time": "2023-04-19T18:31:00Z"
                          }
                        },
                        "stops": "2"
                      }
                    ],
                    "legalight": {
                      "1": {
                        "stop-point-reference": "8503000",
                        "stop-name": {
                          "4": {
                            "text": {
                              "18": "Z\u00fcrich HB"
                            },
                            "language": "de"
                          }
                        },
                        "planned-track": {
                          "4": {
                            "text": {
                              "19": "18"
                            },
                            "language": "de"
                          }
                        },
                        "TRIAS:ESTIMATEDBAY": [
                          {
                            "text": {
                              "20": "9"
                            },
                            "language": "de"
                          }
                        ],
                        "service-arrival": {
                          "2": {
                            "date-time": "2023-04-19T18:56:00Z",
                            "estimated-time": "2023-04-19T18:56:00Z"
                          }
                        },
                        "stops": "3"
                      }
                    },
                    "service": {
                      "1": {
                        "operating-date": "2023-04-19",
                        "journey-reference": "ojp:91005:A:H:j23:729:535",
                        "line-reference": "ojp:91005:A:H",
                        "direction": "outward",
                        "mode": {
                          "1": {
                            "pt-mode": "rail",
                            "sub-mode": "interregionalRail",
                            "name": {
                              "1": {
                                "text": "Zug",
                                "language": "de"
                              }
                            }
                          }
                        },
                        "published-line-name": {
                          "1": {
                            "text": {
                              "21": "IC5"
                            },
                            "language": "de"
                          }
                        },
                        "operation-reference": "ojp:11",
                        "attribute": {
                          "7": {
                            "text": {
                              "7": {
                                "text": "Platzreservierung m\u00f6glich",
                                "language": "de"
                              }
                            },
                            "code": "A___R"
                          },
                          "8": {
                            "text": {
                              "8": {
                                "text": "VELOS: Reservierung obligatorisch",
                                "language": "de"
                              }
                            },
                            "code": "A__VR"
                          },
                          "9": {
                            "text": {
                              "9": {
                                "text": "Businesszone in 1. Klasse",
                                "language": "de"
                              }
                            },
                            "code": "A__BZ"
                          },
                          "10": {
                            "text": {
                              "10": {
                                "text": "Gratis-Internet mit der App SBB FreeSurf",
                                "language": "de"
                              }
                            },
                            "code": "A__FS"
                          },
                          "11": {
                            "text": {
                              "11": {
                                "text": "Familienzone ohne Spielplatz",
                                "language": "de"
                              }
                            },
                            "code": "A__FZ"
                          },
                          "12": {
                            "text": {
                              "12": {
                                "text": "Ruhezone in 1. Klasse",
                                "language": "de"
                              }
                            },
                            "code": "A__RZ"
                          },
                          "13": {
                            "text": {
                              "13": {
                                "text": "Neigezug",
                                "language": "de"
                              }
                            },
                            "code": "A__TT"
                          },
                          "14": {
                            "text": {
                              "14": {
                                "text": "Restaurant",
                                "language": "de"
                              }
                            },
                            "code": "A__WR"
                          },
                          "15": {
                            "text": {
                              "15": {
                                "text": "Aussteigeseite: Links",
                                "language": "de"
                              }
                            },
                            "code": "ojp91005AH_InfoCall729_106652_1"
                          },
                          "16": {
                            "text": {
                              "16": {
                                "text": "Aussteigeseite: Rechts",
                                "language": "de"
                              }
                            },
                            "code": "ojp91005AH_InfoCall729_108276_1"
                          }
                        },
                        "origin-text": {
                          "1": {
                            "language": "de"
                          }
                        },
                        "destination-stop-reference": "8503000",
                        "destination-text": {
                          "1": {
                            "text": {
                              "33": "Z\u00fcrich HB"
                            },
                            "language": "de"
                          }
                        }
                      }
                    },
                    "track": {
                      "1": {
                        "track-section": {
                          "1": {
                            "track-start": {
                              "1": {
                                "stop-point-reference": "8500218",
                                "location-name": {
                                  "2": {
                                    "text": "Olten",
                                    "language": "de"
                                  }
                                }
                              }
                            },
                            "track-end": {
                              "1": {
                                "stop-point-reference": "8503000",
                                "location-name": {
                                  "3": {
                                    "text": "Z\u00fcrich HB",
                                    "language": "de"
                                  }
                                }
                              }
                            },
                            "duration": "PT36M",
                            "length": "54522"
                          }
                        }
                      }
                    }
                  }
                }
              }
            ]
          }
        ]
      },				
    ]
  }
}
```

#### /stationboard ####

    http://transport.gnostx.com/stationboard
    
Request Parameters:

- station (required): Specifies the location of whicht a stationboard should be returned (e.g. "Aarau" or "8507000")
- id: The id of the station whose stationboard should be returned (e.g. 8503000).
- limit: Number of departing connections to return (e.g. 15)
- datetime: Date and time of departing connections, in the format YYYY-MM-DD hh:mm (e.g. 2016-12-23 18:30)
- type: departure (default) or arrival (e.g. arrival)

Important:

- station: Be aware, that ODPCH does not use importance of station. Therefore you can get anything. e.g. Zürich will not get you Zürich HB.
  - if you don't find any results it's advantageous to download the [Verkehrspunktelemente_full.csv](https://opentransportdata.swiss/de/dataset/didok/resource/c76dd45b-260b-4602-b946-f80696c2414b) and look for a matching BPUIC. For example the BPUIC 8515163 (Zürich HB Museumstrasse) delivers no results, but the general Zürich HB BPUIC 8503000 does.
- transportations: not supported

Response Parameters:

- station: ODPCH does not distinguish if it is a station. So you can get an operating point that does you no good.
- stationboard

##### Example request #####

    GET http://transport.gnostx.com/stationboard?id=8507000
    GET http://transport.gnostx.com/stationboard?station=Wankdorf
    GET http://transport.gnostx.com/stationboard?id=8503000&datetime=2016-12-12T22:00:00&type=arrival
    GET http://transport.gnostx.com/stationboard?id=8503000&datetime=2016-12-12T22:00:00&type=departure
    GET http://transport.gnostx.com/stationboard?id=8503000&datetime=2016-12-12T22:00:00&type=arrival&limit=3
    
##### Example response #####

```
"stationboard": {
  "info": "Stationboard station location search contains more than one result. Number of results: 100. The first appearing station with the BPUIC \"8508183\" was taken, with the name \"Madiswil\". If you did not found your desired station, please search more specific like \"?station=Bern Wankdorf Bahnhof\".",
  "results": [
    {
      "result-id": "ID-42F211F6-9EF9-490C-91A6-A8522570B500",
      "stop-event": [
        {
          "call": [
            {
              "call-stop": [
                {
                  "stop-point-reference": "8508183",
                  "stop-name": [
                    {
                      "text": "Madiswil",
                      "language": "de"
                    }
                  ],
                  "planned-track": [
                    {
                      "text": "2",
                      "language": "de"
                    }
                  ],
                  "departure": [
                    {
                      "date-time": "2023-04-19T12:57:00Z",
                      "TRIAS:ESTIMATEDTIME": "2023-04-19T12:59:00Z"
                    }
                  ],
                  "stops": "5"
                }
              ]
            }
          ],
          "service": [
            {
              "operating-date": "2023-04-19",
              "journey-reference": "ojp:91007:D:R:j23:141:21761",
              "line-reference": "ojp:91007:D:R",
              "direction": "return",
              "mode": [
                {
                  "pt-mode": "rail",
                  "sub-mode": "regionalRail",
                  "name": [
                    {
                      "text": "Zug",
                      "language": "de"
                    }
                  ]
                }
              ],
              "published-line-name": [
                {
                  "text": [
                    "S7"
                  ],
                  "language": "de"
                }
              ],
              "operation-reference": "ojp:33",
              "attribute": [
                {
                  "text": [
                    {
                      "text": "Aussteigeseite: Rechts",
                      "language": "de"
                    }
                  ],
                  "code": "ojp91007DR_InfoCall141_111793_1"
                }
              ],
              "origin-stop-reference": "8508187",
              "origin-text": [
                {
                  "text": {
                    "2": "Huttwil"
                  },
                  "language": "de"
                }
              ],
              "destination-stop-reference": "8508100",
              "destination-text": [
                {
                  "text": {
                    "3": "Langenthal"
                  },
                  "language": "de"
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "result-id": "ID-4EE60CE3-C514-41EE-BABC-833ECE5FDBA7",
      "stop-event": {
        "1": {
          "call": {
            "1": {
              "call-stop": {
                "1": {
                  "stop-point-reference": "8508183",
                  "stop-name": {
                    "1": {
                      "text": "Madiswil",
                      "language": "de"
                    }
                  },
                  "planned-track": {
                    "1": {
                      "text": "3",
                      "language": "de"
                    }
                  },
                  "departure": {
                    "1": {
                      "date-time": "2023-04-19T12:59:00Z",
                      "TRIAS:ESTIMATEDTIME": "2023-04-19T12:59:00Z"
                    }
                  },
                  "stops": "5"
                }
              }
            }
          },
          "service": {
            "1": {
              "operating-date": "2023-04-19",
              "journey-reference": "ojp:91007:D:H:j23:41:21754",
              "line-reference": "ojp:91007:D:H",
              "direction": "outward",
              "mode": {
                "1": {
                  "pt-mode": "rail",
                  "sub-mode": "regionalRail",
                  "name": {
                    "1": {
                      "text": "Zug",
                      "language": "de"
                    }
                  }
                }
              },
              "published-line-name": {
                "1": {
                  "text": {
                    "4": "S7"
                  },
                  "language": "de"
                }
              },
              "operation-reference": "ojp:33",
              "attribute": {
                "1": {
                  "text": {
                    "1": {
                      "text": "Aussteigeseite: Rechts",
                      "language": "de"
                    }
                  },
                  "code": "ojp91007DH_InfoCall41_111793_1"
                }
              },
              "origin-stop-reference": "8508100",
              "origin-text": {
                "1": {
                  "text": {
                    "6": "Langenthal"
                  },
                  "language": "de"
                }
              },
              "destination-stop-reference": "8508187",
              "destination-text": {
                "1": {
                  "text": {
                    "7": "Huttwil"
                  },
                  "language": "de"
                }
              }
            }
          }
        }
      }
    },
  ]
}
```

### API Objects ###

#### Location Object####

- type: not supported, set to station (but is not always true)
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

- object not supported

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

- This object not supported.

#### Journey Object ####
Minimalistic support for station board

- passList: not supported
- capacity1st: not supported (set to 1)
- capacity2nd: not supported (set to 1) 

## Source ##

[https://github.com/openTdataCH/poc_transportapi_over_odpch](https://github.com/openTdataCH/poc_transportapi_over_odpch) 
