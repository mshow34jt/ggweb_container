
<?php
#If you are reading this, I am sorry. This code became horrible and needs a rewrite.
        require_once('./conn.php');
        global $db;

		
	 if(isset( $_GET['help'])){
                print("You asked for help\n<br>");
                print(" This page provides point in graphs.\n<br>

                        jobid:\n<br>
                        Uses a subset of nids to generate the data.\n<br>
			*Optional input, default is all nids.\n<br>
			\n<br>
			apid:\n<br>
                        Uses a subset of nids to generate the data.\n<br>
                        *Optional input, default is all nids.\n<br>
                        \n<br>
				
			*optional groupby= -user,project,jobid- sums the values of the nodes in the group\n<br>
			*optional limit= when grouped by category, it limits the columns to the top -limit- values\n<br>
			metric:\n<br>
                        metric to use\n<br>
                        *required\n<br>
			\n<br>
			Available Metrics:\n<br>
		\n<br>");
                        $query = "select * from metrics_md";
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
                                echo "$row[1]<br/>" ;
                                $index++;
                        }
                die();
        }

	$queryStart=time();
	
	if(isset( $_GET['groupby']))
	{
		$groupby=$_GET['groupby'];
		$xAxisString=$groupby;

	}
	else 
	{
		$xAxisString='Nodes';
	}

        if(isset( $_GET['metric'])){
		$metric = $_GET['metric'];
		if($metric=="flops")
		{
			$tableName="ovis_flops";
			$metricString="GFlops";
			$metricDivisor=1000000000;
			$metricUnits="GFlops";
			$metricDescription="Gigflops";
		}
		else
		{
			$tableName="ovis_metrics";
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
	}
	else
	{
		die('Error: No Metric set');
	}

	 if(isset( $_GET['limit'])){
                $limitset= $_GET['limit'];
#               $timeset=1;
                 $limit=$limitset;
        }
        else
        {
                $limitset=0;
	}	

        if(isset( $_GET['time'])){
                $timeset = $_GET['time'];
#		$timeset=1;
		 $time=$timeset;
        }
        else
        {
		$timeset=0;
                $query = "select distinct cTime from $metricTable order by cTime desc limit 1";
                $result = mysqli_query( $db,$query );
                if (!$result)
                          die( "Error: ".mysql_error().":\n   ".$query);
                $index = 0;
                while($row = mysqli_fetch_row($result)) {
                        $time = $row[0];
                        #echo "$name<br/>" ;
                        $index++;
                }

        }
	$apdata=0;
	 if(isset( $_GET['apid'])and !empty($_GET["apid"])){
                 #get a reduced nodeset defined by the jobid
                $apid = $_GET['apid'];
                $apdata=1;
                $nidlist=array();
		if($apid>0)
		{
			$query = "call prep_apid_table('$apid')";
			$result2 = mysqli_query ( $db, $query );
                        if (! $result2)
                                die ( "Error creating tmp hosts: " . mysql_error () . ":\n   " . $query );
                        $query = "SELECT nid FROM TMP_HOSTS";
                        $nidresult = mysqli_query( $db,$query );
                        if (!$nidresult)
                                die( "Error query TMP Hosts: ".mysql_error().":\n   ".$query);
			$numnodes=mysqli_num_rows($nidresult);	
			if($numnodes)
                	{
                        	$index = 0;
                        	while($row = mysqli_fetch_row($nidresult)) {
                                	array_push($nidlist,$row[0]);
                                	#echo $nidlist[$index];
                                	$index++;
                        	}
                        }
                	else
                	{
                        	die( "$jobid Not a valid apid\n");
                	}
		}	
		else
			$apdata=0;
	}	

	if(isset( $_GET['jobid'])and !empty($_GET["jobid"])){
		 #get a reduced nodeset defined by the jobid
    	       	$jobid = $_GET['jobid'];
		$jobdata=1;
		$nidlist=array();


                if ($jobid == "compute" or $jobid == "dsl" or $jobid == "rsip" or $jobid == "lnet" or $jobid == "network" or $jobid == "mom" or $jobid == "service") {
        		$query = "call prep_nid_table('$jobid')";
        		$result2 = mysqli_query ( $db, $query );
        		if (! $result2)
                		die ( "Error creating tmp hosts: " . mysql_error () . ":\n   " . $query );
			$query = "SELECT nid FROM TMP_NIDSET";
                        $nidresult = mysqli_query( $db,$query );
                        if (!$nidresult)
                                die( "Error query TMP Hosts: ".mysql_error().":\n   ".$query);
			$realJob = 0;

		}
		else{	
       	       		$query = "SELECT nid FROM job_hosts WHERE jobid=$jobid";
               		$nidresult = mysqli_query( $db,$query );
               		if (!$nidresult)
                     		die( "Error: ".mysql_error().":\n   ".$query);
			$realJob=1;
		}
               	$numnodes=mysqli_num_rows($nidresult);
		if($numnodes)
		{
			$index = 0;
   			while($row = mysqli_fetch_row($nidresult)) {
               			array_push($nidlist,$row[0]);
				#echo $nidlist[$index];
               			$index++;
       			}
			if($realJob)
			{
				$query = "SELECT status FROM jobs WHERE jobid=$jobid";
                        	$result = mysqli_query( $db,$query );
				$row = mysqli_fetch_row($result);
				if($row[0]=="Running"){
					print "$numnodes nodes in job $jobid<br />";
				}
				else{
					echo "Caution, the job state is:".$row[0]."<br />";
				}
			}
		}
		
		else
		{
			die( "$jobid Not a valid Jobid\n");
		}
		

        }
        else
        {
		$jobdata=0;
		#find job data for the nids though
		$jobs=array();
		$jobs_data=array();
		$nidJob=array();

#		$query = "SELECT jobid,user,account FROM jobs WHERE (start < $time AND start > $time-172800) AND (end IS NULL OR (end IS NOT NULL AND end >$time)) ";
		$query = "SELECT jobid,user,account FROM jobs WHERE (start < $time AND start > $time-604800) AND (end IS NULL OR (end IS NOT NULL AND end >$time)) ";
# too long		$query = "SELECT jobid,user,account FROM jobs WHERE ((end IS NULL OR (end IS NOT NULL AND end >$time)) AND start < $time ) ";
                $result = mysqli_query( $db,$query );
                if (!$result)
                        die( "Error: ".mysql_error().":\n   ".$query);

                $index=0;
                while($row = mysqli_fetch_row($result)) {
                        array_push($jobs,$row[0]);
                        #array_push($jobs_data,array($row[0],$row[1],$row[2]));
			$jobs_data[$row[0]]=array($row[1],$row[2]);
                        $index++;

		}
#		echo $index." Jobs running".$row[0]."<br />";	
#		foreach($jobs_data as $x => $x_value) {
#    			echo "Key=" . $x . ", Value0=" . $x_value[0]. " Value1=".$x_value[1];
#    			echo "<br>";
#		}

#	       foreach($jobs_data as $tmpJob)
#		{
#			print "job=$tmpJob[0] user=$tmpJob[1] account=$tmpJob[2] <br />";	
#		}
               foreach($jobs as $tmpJob)
               {
                       $query = "SELECT nid FROM job_hosts WHERE jobid=$tmpJob";
                        $result = mysqli_query( $db,$query );
                        while($row = mysqli_fetch_row($result)) {
                               $nid=$row[0];
                               $nidJob[$nid]=$tmpJob;
                       }



               }

        }
	

	if($metric=="flops")
	{
		$query = "select nid,flops from ovis_flops where utime  between $time-30 and $time+30";
	}
	else
	{	
		$query = "select CompId,$metric from $metricTable where cTime  between $time-30 and $time+30 ";
	}
        $result = mysqli_query( $db,$query );
        if (!$result)
                  die( "Error: ".mysql_error().":\n   ".$query);
        $index = 0;

	$num_results=mysqli_num_rows($result);
	#print "$num_results Entries for $time <br />";


	#check and make sure you got dota for most of the machine. If not, then it could be because a small number of machines have a newer timestamp
	#therefore, get the top 2 timestamps (FYI, this shoud probably loop and increase the value of the limit.
#	if($num_results<20000){
#		 $query = "select distinct cTime from ovis_metrics  order by cTime desc limit 2";
#	        $result = mysqli_query( $db,$query );
#        	if (!$result)
#                  	die( "Error: ".mysql_error().":\n   ".$query);
#        	$index = 0;
#        	while($row = mysqli_fetch_row($result)) {
#                	$time = $row[0];
#                	#echo "$name<br/>" ;
#                	$index++;
#        	}
#
#		$time=$time-30;
#	 	$query = "select CompId,$metric from ovis_metrics where cTime >= $time";
#        	$result = mysqli_query( $db,$query );
#        	if (!$result)
#                  die( "Error: ".mysql_error().":\n   ".$query);
#		$num_results=mysqli_num_rows($result);
        	#print "Now We have $num_results Entries for $time <br />";
#	}
	$datetime=date('r',$time);
	echo $datetime.'<br />';
#	echo "$metric $metricString $metricUnits $metricDivisor $metricDescription <br/>";
	$nidval=array();
	$jobSum=array();
	$userSum=array();
	$projectSum=array();
	$tmpJobDat=array();
	$index=0;
        while($row = mysqli_fetch_row($result)) {
		$nid=$row[0];
		$val=$row[1];
		$nidval[$nid]= $val;
		if(isset($nidJob[$nid]))
                {
		 	$tmpJobDat=$jobs_data[$nidJob[$nid]];
			if(isset($jobSum[$nidJob[$nid]]))
				$jobSum[$nidJob[$nid]] += $val;
			else
				$jobSum[$nidJob[$nid]]=$val;

                        if(isset($userSum[$tmpJobDat[0]]))
                                $userSum[$tmpJobDat[0]] += $val;
                        else
                                $userSum[$tmpJobDat[0]]=$val;

                        if(isset($projectSum[$tmpJobDat[1]]))
                                $projectSum[$tmpJobDat[1]] += $val;
                        else
                                $projectSum[$tmpJobDat[1]]=$val;


		}
		

                $index++;
        }


     $queryEnd=time();
     $queryTime=$queryEnd-$queryStart;
     arsort($jobSum);
     arsort($userSum);
     arsort($projectSum);
     print ("Data Query took $queryTime seconds\n <br>");
	
print "<html>
  <head>
    <script type=\"text/javascript\" src=\"//www.google.com/jsapi\"></script>
    <script type=\"text/javascript\">
      google.load(\"visualization\", \"1\", {packages:[\"corechart\"]});
      google.setOnLoadCallback(drawChart);
      var chart;
      var data;
      var currentData;
      var jobidData;
      var userData;
      var projectData;
      var options;

      function drawChart() {
        data = google.visualization.arrayToDataTable([
          ['$xAxisString', '$metricString', 'tooltip'],";

	if($jobdata or $apdata){
		for($x=0;$x<$numnodes;$x++){
			$index=$x+1;
			$nid=$nidlist[$x];
			if(isset($nidval[$nid])){
				$val=($nidval[$nid])/$metricDivisor;
			}
			else
			{
				$val=0;
			}
			echo "[$index,$val,'nid=$nid:val=$val'],\n";
	
		}
	}
	else
	{

		if(isset($groupby))
		{
			if($groupby=='jobid')
			{
				$index=1;
				foreach($jobSum as $x =>$x_value)
				{
					$tmpJob=$x;
                                	$tmpJobDat=$jobs_data[$tmpJob];
					$val=$x_value/$metricDivisor;
					if($limitset==0||$index<=$limitset)
                                		echo "['$x',$val,'val=$val:jobid=$x:user=$tmpJobDat[0]:Account=$tmpJobDat[1]'],\n";
					$index++;
				}
			}

			else if($groupby=='user')
                        {
				$index=1;
                                foreach($userSum as $x =>$x_value)
                                {
                                        $val=$x_value/$metricDivisor;
					if($limitset==0||$index<=$limitset)
                                        	echo "['$x',$val,'val=$val:user=$x'],\n";
					$index++;
                                }
                        }
                        else if($groupby=='project')
                        {
				$index=1;
                                foreach($projectSum as $x =>$x_value)
                                {
                                        $val=$x_value/$metricDivisor;
					if($limitset==0||$index<=$limitset)
                                        	echo "['$x',$val,'val=$val:project=$x'],\n";
					$index++;
                                }
                        }

		}
		else
		{
			foreach($nidval as $x =>$x_value){
				if(isset($nidJob[$x]))
				{
					$tmpJob=$nidJob[$x];
					$tmpJobDat=$jobs_data[$tmpJob];
				}
				else
				{
					$tmpJob=0;
					$tmpJobDat=array(0,0);
				}
				$val=$x_value/$metricDivisor;
				echo "[$x,$val,'nid=$x:val=$val:job=$tmpJob:user=$tmpJobDat[0]:Account=$tmpJobDat[1]'],\n";
				}
		}
	}
#	echo "[0,0,'$index']\n";
	print"	        ])
	
	jobidData = google.visualization.arrayToDataTable([
          ['JobID', '$metricString', 'tooltip'],";

        $index=1;	
	foreach($jobSum as $x =>$x_value)
                                {
                                        $tmpJob=$x;
                                        $tmpJobDat=$jobs_data[$tmpJob];
                                        $val=$x_value/$metricDivisor;
					if($limitset==0||$index<=$limitset)
                                        	echo "['$x',$val,'val=$val:jobid=$x:user=$tmpJobDat[0]:Account=$tmpJobDat[1]'],\n";
					$index++;
                                }
	print "])

	userData = google.visualization.arrayToDataTable([
          ['User', '$metricString', 'tooltip'],";
	 $index=1;
	 foreach($userSum as $x =>$x_value)
                                {
                                        $val=$x_value/$metricDivisor;
					if($limitset==0||$index<=$limitset)	
                                        	echo "['$x',$val,'val=$val:user=$x'],\n";
					$index++;
                                }
        print "])

	projectData = google.visualization.arrayToDataTable([
          ['Project', '$metricString', 'tooltip'],";
	 $index=1;
	 foreach($projectSum as $x =>$x_value)
                                {
                                        $val=$x_value/$metricDivisor;
					if($limitset==0||$index<=$limitset)
                                        	echo "['$x',$val,'val=$val:project=$x'],\n";
					$index++;
                                }

        print "])


	

         options = {
          title: '$metricString',
	  hAxis: {title: '$xAxisString',  titleTextStyle: {color: 'red'}},
	  vAxis: {title: '$metricUnits',  titleTextStyle: {color: 'red'}},
	  chartArea: { width: '90%',height:'80%' },
	  bars: 'vertical'
        };

        chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

	data.setColumnProperty(2, 'role', 'tooltip');
	jobidData.setColumnProperty(2, 'role', 'tooltip');
	userData.setColumnProperty(2, 'role', 'tooltip');
	projectData.setColumnProperty(2, 'role', 'tooltip');
        currentData=data;
        chart.draw(data, options);

	google.visualization.events.addListener(chart, 'select', selectHandler);
	
	function selectHandler() {
 	 var selection = chart.getSelection();
	var tooltip=data.getValue(selection[0].row,  selection[0].column + 1);

	var splitTooltip=tooltip.split(':');
	var nid=0;
	var jobid=0;
	var time=0;

	for (i=0;i<splitTooltip.length;i++){
		if(splitTooltip[i].indexOf('nid')>-1){
			var tmp=splitTooltip[i].split('=');
			toolNid=tmp[1];	
		}
		else if (splitTooltip[i].indexOf('job')>-1){
                        var tmp=splitTooltip[i].split('=');
                        jobid=tmp[1];
                }
		else if (splitTooltip[i].indexOf('time')>-1){
                        var tmp=splitTooltip[i].split('=');
                        time=tmp[1];
                }
		

	}
	";
#	if ($jobid>0)
#	{
#		print "var url='//isce.ncsa.illinois.edu/ovis/jobchart.php?metric=$metric;";
#	}
#	else
#	{
		 print "var url='//isce.ncsa.illinois.edu/ovis/jobchart.php?metric=$metric&time=$time&start=-.1&nid='+toolNid;";	
#	}
	print "	
	window.open(url);

	}	
	window.addEventListener('resize', function() { chart.draw(currentData, options); }, false);	
	}
	function setJobidData()
	{
	

		 options = {
          	title: '$metricString',
          	hAxis: {title: 'Jobid',  titleTextStyle: {color: 'red'}},
          	vAxis: {title: '$metricUnits',  titleTextStyle: {color: 'red'}},
          	width: 1200,
          	height: 800,
          	bars: 'vertical'
 	       };
		currentData=jobidData;
		 chart.draw(jobidData, options);
	}
	function setUserData()
        {

		 options = {
          	title: '$metricString',
          	hAxis: {title: 'User',  titleTextStyle: {color: 'red'}},
          	vAxis: {title: '$metricUnits',  titleTextStyle: {color: 'red'}},
          	width: 1200,
          	height: 800,
          	bars: 'vertical'
        	};		
		currentData=userData;
                chart.draw(userData, options);
		

        }
       function setProjectData()
        {
		  options = {
                title: '$metricString',
                hAxis: {title: 'Project',  titleTextStyle: {color: 'red'}},
                vAxis: {title: '$metricUnits',  titleTextStyle: {color: 'red'}},
                width: 1200,
                height: 800,
                bars: 'vertical'
                };
		currentData=projectData;
                chart.draw(projectData, options);
        }
       function setNoneData()
        {
		  options = {
                title: '$metricString',
                hAxis: {title: 'Nodes',  titleTextStyle: {color: 'red'}},
                vAxis: {title: '$metricUnits',  titleTextStyle: {color: 'red'}},
                width: 1200,
                height: 800,
                bars: 'vertical'
                };
		currentData=data;
                chart.draw(data, options);
        }
    </script>
  </head>
  <body>
    
    <form>
	";
	if ($jobdata)
	{
		echo "Job Data";
	}
	else 
	{
	print "
  	<input type=\"radio\" name=\"groupby\" value=\"none\" onclick=\"setNoneData();\" checked > None<br>
  	<input type=\"radio\" name=\"groupby\" value=\"jobid\" onclick=\"setJobidData();\"> Jobid<br>
  	<input type=\"radio\" name=\"groupby\" value=\"project\" onclick=\"setProjectData();\"> Project<br>
  	<input type=\"radio\" name=\"groupby\" value=\"user\" onclick=\"setUserData();\"> User";
	}
    print "
    </form>
	<br>
	<div id=\"chart_div\" style=\"width: 100%; height: 80%\"></div>
  </body>
</html>";




?>
