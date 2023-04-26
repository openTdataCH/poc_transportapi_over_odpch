<?php
/*
    Copyright 2016 Matthias Günter, GnostX GmbH

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
*/

/*
The template is the XML request with "variables" in the form of %%%VAR%%%.
*/

$triprequestxml = <<<END
<Trias version="1.1" xmlns="http://www.vdv.de/trias" xmlns:siri="http://www.siri.org.uk/siri" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ServiceRequest>
        <siri:RequestTimestamp>%%%REQUEST_TIMESTAMP%%%</siri:RequestTimestamp>
        <siri:RequestorRef>API-Explorer</siri:RequestorRef>
        <RequestPayload>
            <TripRequest>
                <Origin>
                    <LocationRef>
                        <StopPointRef>%%%StartBPUIC%%%</StopPointRef>
                    </LocationRef>
                    %%%StartDateTime%%%
                </Origin>
                <Destination>
                    <LocationRef>
                        <StopPointRef>%%%StopBPUIC%%%</StopPointRef>
                    </LocationRef>
                    %%%StopDateTime%%%
                </Destination>
                <Params>
                    <NumberOfResults>%%%NumRES%%%</NumberOfResults>
                    <IncludeTrackSections>true</IncludeTrackSections>
                    <IncludeLegProjection>true</IncludeLegProjection>
                    <IncludeIntermediateStops>true</IncludeIntermediateStops>
                </Params>
            </TripRequest>
        </RequestPayload>
    </ServiceRequest>
</Trias>
END;

?>
