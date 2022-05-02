<?php
        require_once('./conn.php');
        global $db;



	if(isset( $_GET['help'])){
                print("You asked for help\n<br>");
                print(" This page provides Time series graphs where the data point is a collection of points in time aggreagated by a parameter called calc.\n<br>
			
			jobid:\n<br>	
			Uses a subset of nids to generate the data. Uses the job start time to denote the beginning of the data if not overridden by start=, if the job has ended, the dataset ends at job end time. \n<br>
			*Optional input, but if not specified, requires start and nid inputs\n<br>
			\n<br>
			start:\n<br>	
			Sets the start time of the data. Positive numbers are interpreted as epoch. Negative are interpreted and number of days in the past from current time\n<br>
			*optional if jobid is specified\n<br>
			\n<br>
			end:\n<br>
			End time in Epoch time\n<br>
			\n<br>
			metric:\n<br>	
			metric to use\n<br>
			*required\n<br>
			\n<br>
			length:	\n<br>
			Sets the data end time to the start plus the number of seconds\n<br>
			*optional\n<br>
			\n<br>
			nid:\n<br>	
			If jobid is not specified, you can provide a single nid, or the keyword all for all compute and service nodes exclusive from jobid\n<br>
			nid can also use a node type such as the following\n<br>");
#			$query = "select distinct class from nidmap_current";
#                        $result = mysqli_query( $db,$query );
#                        if (!$result)
#                          die( "Error: ".mysql_error().":\n   ".$query);
#                        while($row = mysqli_fetch_row($result)) {
#                                echo "$row[0]\n<br/>" ;
#                        }	


			print("
			\n<br>
			apid:\n<br> 
			use nodes from a given apid\n<br>
			*optional\n<br>
			\n<br>
			calc:\n<br>	
			This determines how the data is combined for each point in time. examples are sum,min,max.avg,std\n<br>
			*optional, default=sum\n<br>		
			\n<br>
			csv:\n<br>
			Return the data in csv without a chart\n<br>
			\n<br>
			height:\n<br>
			Height of the chart in pixels\n<br>
			width:\n<br>
			Width of the chart in pixels\n<br>
			\n<br>
			Defaults\n<br>
			End time (if not set by a finished jobid) is assumed to be the current time.\n<br>
			Aggregation method is sum all data points for the given time\n<br>
                        \n<br> 
			Available metrics:\n<br>
			\n<br>");
			$query = "select * from metrics_md where hidden=0";
                	$result = mysqli_query( $db,$query );
                	if (!$result)
                          die( "Error: ".mysql_error().":\n   ".$query);
                	$index = 0;
                	while($row = mysqli_fetch_row($result)) {
                        	$metricString=$row[2];
                        	$metricDivisor=$row[3];
                        	$metricUnits=$row[4];
                        	$metricDescription=$row[5];
                        	echo "$row[1]<br/>" ;
                        	$index++;
                	}
                die();
        }


	if(isset( $_GET['csv'])){
                $csvonly = 1;
        }
        else
        {
                $csvonly=0;
        }

        if(isset( $_GET['metric'])){
                $metric = $_GET['metric'];
		$query = "select * from metrics_md where name='$metric'";
                $result = mysqli_query( $db,$query );
                if (!$result)
                          die( "Error: ".mysql_error().":\n   ".$query);
                $index = 0;
                while($row = mysqli_fetch_row($result)) {
                        $metricString=$row[2];
                        $metricDivisor=$row[3];
                        $metricUnits=$row[4];
                        $metricDescription=$row[5];
                        $metricTable=$row[7];
                        #echo "$name<br/>" ;
                        $index++;
                }
		
        }
        else
        {
                die('Error: No Metric set');
        }

	if(isset( $_GET['apid'])){
                $apid = $_GET['apid'];
        }
        else
        {
                $apid=0;
        }

        if(isset( $_GET['jobid'])){
                $jobid = $_GET['jobid'];
        }
        else
        { 	
		$jobid=0;	
		#if the job isn't set, you need both a start time and nid
		if( !isset( $_GET['apid']))
		{
			if(!isset( $_GET['nid']) or !isset( $_GET['start']) )
                		die('Error: No jobid or apid  set, therefore a nid and start time required');
		}
        }


	if(isset( $_GET['nid'])and !empty($_GET["nid"])){
                $nidset = $_GET['nid'];
		if($nidset=="all")
		{
			$allnodeset=1;
			$nidgroup=0;
		}
		else if($nidset>0&&$nidset<28000)
                {
                 #       print("int nid\n<br>");
                        $allnodeset=0;
                        $nidgroup=0;
                }
		else #if (!is_int($nidset)) #some string other than a nodeid	
		{
		#	print("Non int nid\n<br>");
			$allnodeset=0;
			$nidgroup=1;
		}
        }
        else
        {
                $nidset=0;
		$nidgroup=0;
        }
	
        if(isset( $_GET['start'])and !empty($_GET["start"])){
                $startset = $_GET['start'];
        }
        else
        {
 	        $startset=0;
        }

	if(isset( $_GET['end'])and !empty($_GET["end"])){
                $endset = $_GET['end'];
        }
        else
        {
                $endset=0;
        }




        if(isset( $_GET['length'])and !empty($_GET["length"])){
                $lengthset = $_GET['length'];
        }
        else
        {
                $lengthset=0;
        }

        if(isset( $_GET['calc'])and !empty($_GET['calc'])){
                $calc = $_GET['calc'];
		if($calc=="count")
			$metricDivisor=1;

        }
        else
        {
                 $calc="SUM";
		 if(!$csvonly)
                 	print("No data reduction selected via calc= , using SUM\n <br>");
        }



        if(isset( $_GET['width'])and !empty($_GET['width'])){
		$sizeset=1;
                $width = $_GET['width'];
                $width=$width;
                $widthtxt=$width."px";
        }
        else
        {	
		$sizeset=0;
                $width=1000;
                $widthtxt=$width."px";
        }

        if(isset( $_GET['height'])and !empty($_GET['height'])){
		$sizeset=1;
                $height = $_GET['height'];
                $heighttxt=$height."px";
        }
        else
        {
		$sizeset=0;
                $height=700;
                $heighttxt=$height."px";
        }	
	

	if($nidgroup)
	{
		$query = "call prep_nid_table('$nidset')";
                $result = mysqli_query( $db,$query );
		 if (!$result)
                          die( "Error: ".mysql_error().":\n   ".$query);
		if(!$csvonly)
		print("Prepping node table for $nidset<br>");
		
	}

	if($jobid){

		$query = "call prep_job_table($jobid)";
		$result = mysqli_query( $db,$query );
                if (!$result)
                          die( "Error: ".mysql_error().":\n   ".$query);
                $index = 0;

	
		$query = "select start,end from jobs where jobid=$jobid";

		$result = mysqli_query( $db,$query );
	        if (!$result)
        	          die( "Error: ".mysql_error().":\n   ".$query);
	        $index = 0;

        	while($row = mysqli_fetch_row($result)) {
                	$start = $row[0];
                	$end = $row[1];
           	       $index++;
        	}
		if($end<1)
		{	
			if(!$csvonly)
				print("Job $jobid is still running\n<br>");
			$end=time();
		}

	}
#apid data

        if($apid){

                $query = "call prep_apid_table($apid)";
                $result = mysqli_query( $db,$query );
                if (!$result)
                          die( "Error: ".mysql_error().":\n   ".$query);
                $index = 0;


                $query = "select u_start,u_end from apruns where apid=$apid";

                $result = mysqli_query( $db,$query );
                if (!$result)
                          die( "Error: ".mysql_error().":\n   ".$query);
                $index = 0;

                while($row = mysqli_fetch_row($result)) {
                        $start = $row[0];
                        $end = $row[1];
                       $index++;
                }
                if($end<1)
                {
                        if(!$csvonly)
                                print("apid $apid is still running\n<br>");
                        $end=time();
                }

        }

# if the start or end has been defined in the url, overwrite the job info	
	if($startset)
               if($startset>0)
                        $start=$startset;
                else
		{
			if($jobid)	
				 $start=$end+$startset*86400;
			else
                        	$start=time()+$startset*86400;
		}
	if($lengthset)
		$end=$start+$lengthset;
	else
	{
		if(!$jobid && !$apid)
			$end=time();
	}

	if($endset)
		$end=$endset;


	$startString=date('r',$start);
	$endString=date('r',$end);
	if(!$csvonly)
		print("Start=$startString <br> End =$endString <br>");
	$queryStart=time();
	if(!$nidset){
		$query = "select ((Ctime DIV 60)*60) as minutex,$calc($metric) from $metricTable where cTime > $start AND cTime<$end AND CompId IN (select nid from TMP_HOSTS) group by minutex";
	}

	else
	{
                if($allnodeset)
                        $query = "select ((Ctime DIV 60)*60) as minutex,$calc($metric) from $metricTable  where cTime > $start AND cTime<$end group by minutex";
		else if ($nidgroup)
			$query = "select ((Ctime DIV 60)*60) as minutex,$calc($metric) from $metricTable  where cTime > $start AND cTime<$end AND CompId IN (select nid from TMP_NIDSET) group by minutex";
		else
			$query = "select ((Ctime DIV 60)*60) as minutex,$metric from $metricTable  where cTime > $start AND cTime<$end AND CompId=$nidset group by minutex";

	}

	$result = mysqli_query( $db,$query );
                if (!$result)
                  die( "Error: ".mysql_error().":\n   ".$query);
                $num_results=mysqli_num_rows($result);

	$queryEnd=time();
	$queryTime=$queryEnd-$queryStart;
	if(!$csvonly)
		print ("Data Query took $queryTime seconds\n <br>"); 



if (!$csvonly)
print("
 <html>
  <head>
 <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
 <script type='text/javascript'>");

	if (!$csvonly)	print(" google.load('visualization', '1', {'packages':['annotatedtimeline']});");
if (!$csvonly) print("

      google.setOnLoadCallback(drawChart);
      var data;
      function drawChart() {
         data = new google.visualization.DataTable();\n");
#print("       data.addColumn('Time', '$metric', 'tooltip');\n");
if (!$csvonly) print("        data.addColumn('datetime', 'Date');\n");
if (!$csvonly) print("           data.addColumn('number', '$metricString ($metricUnits)');\n
		  data.addColumn('string', 'tooltip');\n data.addRows([");
		


	$index=0;
	$valSum=0;
	$firstTime=0;
	$lastTime=0;
        while($row = mysqli_fetch_row($result)) {
                $xTime=$row[0];
		if(!$xTime)
			$xTime='0';	
		
                $yVal=$row[1]/$metricDivisor;
		$valSum+=$yVal;
		////$tString=date('H:i',$xTime);
		$tString=date('r',$xTime);
		#$tString=date('r',$xTime);
#		print(",\n['$tString', $yVal, '$metric=$yVal:time=$xTime']");
		if ($index==0)
		{
			$firstTime=$xTime;
			if($csvonly)
			{
				print"$xTime,$yVal";
			}
			else
			{
                        	print"\n[new Date($xTime*1000), $yVal, '$metric=$yVal:time=$xTime:$tString']";
			}
		}
                else
		{
			$LastTime=$xTime;
			 if($csvonly)
                        {
				print"\n$xTime,$yVal";
                        }
                        else
                        {
                        	print",\n[new Date($xTime*1000), $yVal,'$metric=$yVal:time=$xTime:$tString']";
			}
		}

                $index++;
        }

	$avgVal=$valSum/$index;
	
if (!$csvonly)		
print       (" ]);

        var options = {
          title: '$metric',
	  scaleType: 'maximized',
          legend: { position: 'bottom' },
	  explorer: { 
            actions: ['dragToZoom', 'rightClickToReset'],
            axis: 'horizontal',
            keepInBounds: true,
            maxZoomin: 20.0},
	  chartArea: { width: '90%', height: '80%'}
        };
");
	if (!$csvonly)	print("var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('time_chart'));");
	if (!$csvonly)	 print("var chart = new google.visualization.LineChart(document.getElementById('time_chart'));");
if (!$csvonly) print("

	data.setColumnProperty(2, 'role', 'tooltip');

        chart.draw(data,options );


	google.visualization.events.addListener(chart, 'select', selectHandler);

        function selectHandler() {

	var selection = chart.getSelection();
        var tooltip=data.getValue(selection[0].row,  selection[0].column + 1);


        var splitTooltip=tooltip.split(':');
        var time=0;

        for (i=0;i<splitTooltip.length;i++){
                if (splitTooltip[i].indexOf('time')>-1){
                        var tmp=splitTooltip[i].split('=');
                        time=tmp[1];
                }


        }
   

	var url='ovistime.php?metric=$metric&time='+time+'&jobid=$jobid&apid=$apid';

	window.open(url);

	}
	
	window.addEventListener('resize', function() { chart.draw(data, options); }, false);
      }
  
    function downloadCSV(filename) {
        jsonDataTable = data.toJSON();
        var jsonObj = eval('(' + jsonDataTable + ')'); 
        output = JSONObjtoCSV(jsonObj,filename);
    }

    function JSONObjtoCSV(jsonObj, filename){
    filename = filename ;
        var body = '';      var j = 0;
        var columnObj = []; var columnLabel = []; var columnType = [];
        var columnRole = [];    var outputLabel = []; var outputList = [];
        for(var i=0; i<jsonObj.cols.length; i++){
            columnObj = jsonObj.cols[i];
            columnLabel[i] = columnObj.label;
            columnType[i] = columnObj.type;
            columnRole[i] = columnObj.role;
            if (columnRole[i] == null) {
                outputLabel[j] = '\"' + columnObj.label + '\"';
                outputList[j] = i;
                j++;
            }           
        }
        body += outputLabel.join(\",\") + String.fromCharCode(13);

        for(var thisRow=0; thisRow<jsonObj.rows.length; thisRow++){
            outputData = [];
            for(var k=0; k<outputList.length; k++){
                var thisColumn = outputList[k];
                var thisType = columnType[thisColumn];
                thisValue = jsonObj.rows[thisRow].c[thisColumn].v;
                switch(thisType) {
                    case 'string':
                        outputData[k] = '\"' + thisValue + '\"'; break;
                    case 'datetime':
                        thisDateTime = eval(\"new \" + thisValue);
                        outputData[k] = '\"' + thisDateTime.getFullYear() + '-' + (\"0\" + (thisDateTime.getMonth()+1)).slice(-2) + '-' + (\"0\" + thisDateTime.getDate()).slice(-2) + ' ' + (\"0\" + thisDateTime.getHours()).slice(-2) + ':' + (\"0\" + thisDateTime.getMinutes()).slice(-2) + ':' + (\"0\" + thisDateTime.getSeconds()).slice(-2) + '\"';  
                        delete window.thisDateTime;
                        break;
                    default:
                        outputData[k] = thisValue;
                }
            }
            body += outputData.join(\",\");
            body += String.fromCharCode(13);
        }       
        uriContent = \"data:text/csv;filename=filename.csv,\" + encodeURIComponent(body);
        newWindow=downloadWithName(uriContent, filename);
        return(body);
    }

    function downloadWithName(uri, name) {
     // //stackoverflow.com/questions/283956/is-there-any-way-to-specify-a-suggested-filename-when-using-data-uri
    function eventFire(el, etype){
        if (el.fireEvent) {
            (el.fireEvent('on' + etype));
        } else {
            var evObj = document.createEvent('Events');
            evObj.initEvent(etype, true, false);
            el.dispatchEvent(evObj);
        }
    }
    var link = document.createElement(\"a\");
    link.download = name;
    link.href = uri;
    eventFire(link, \"click\");
    }

    </script>");
	     if(!$csvonly) print ("Average value is $avgVal \n <br>");
if (!$csvonly) print("
  </head>
  <body>
");
if($sizeset)
    print("
    <div id=\"time_chart\" style=\"width: $widthtxt; height: $heighttxt\"></div>");
else
    print("<div id=\"time_chart\" style=\"width: 100%; height: 80%\"></div>");
    print("
    <button id=\"CSVDownload\" onclick=\"downloadCSV('$jobid$metric.csv')\" title=\"Download to CSV\">Download to CSV</Button>
  </body>
</html> ");


?>
