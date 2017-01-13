<? 
// MODULE_NAME: Market Graphs
// MODULE_DESC: Graphs of the resource prices per server
// MODULE_STATUS: Released
// MODULE_VERSION: 1
// MarketDisplay.php - Displays Highstock Graph
// TECH 20130704
include_once "StandardIncludes.php";
setlocale(LC_ALL,'');
date_default_timezone_set("America/Chicago");
$DBPATH=$NEATO_DBDIR."/MarketPrices.db3";

try {
   $File_DB = new PDO('sqlite:'.$DBPATH);
   $SQLStatement = "select distinct mpServer from MarketPrices group by mpServer;";
   $result = $File_DB->query($SQLStatement);
   $servers = array();
   while ($row = $result->fetch()) {
      array_push($servers,$row[0]);
   }
   // Clean up after ourselves, close the database
   $File_DB = null;
} catch(PDOException $e) {
    // Print PDOException message
    echo "Uh oh, Scooby! ".$e->getMessage();
}
$server=$servers[0];
if (isset($_COOKIE['server'])) {
   $server=$_COOKIE['server'];
   }
if (isset($_GET['server'])) {
   $server = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['server']);
   setcookie("server", $server, time()+3600);
}
?><!DOCTYPE HTML>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <title>MarketDisplay <?=$server;?></title>
      <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
      <script type="text/javascript"><?
      $resources = array("Food","Lumber","Stone","Iron");
      foreach ($resources as $res) { ?>
$(function() {
   $.getJSON('<?=$NEATO_HTTPURL?>/GetPricing.php?server=<?=$server;?>&res=<?=$res;?>&callback=?', function(data) {
      // create the chart
      $('#container<?=$res;?>').highcharts('StockChart', {
         title: {
            text: '<?=$server;?> <?=$res;?> price by minute'
         }, 
         xAxis: {
            gapGridLineWidth: 0
         },    
         rangeSelector : {
            buttons : [{
               type : 'hour',
               count : 1,
               text : '1h'
            }, {
               type : 'hour',
               count : 2,
               text : '2h'
            },{
               type : 'hour',
               count : 6,
               text : '6h'
            },{
               type : 'hour',
               count : 12,
               text : '12h'
            },{
               type : 'day',
               count : 1,
               text : '1D'
            }, {
               type : 'all',
               count : 1,
               text : 'All'
            }],
            selected : 2,
            inputEnabled : false
         },       
         series : [{
            name : '<?=$res;?>',
            type: 'area',
            data : data,
            gapSize: 5,
            tooltip: {
               valueDecimals: 2
            },
            fillColor : {
               linearGradient : {
                  x1: 0, 
                  y1: 0, 
                  x2: 0, 
                  y2: 1
               },
               stops : [[0, Highcharts.getOptions().colors[0]], [1, 'rgba(0,0,0,0)']]
            },
            threshold: null
         }]
      });
   });
   /**
 * Dark blue theme for Highcharts JS
 * @author Torstein H�nsi
 */

Highcharts.theme = {
   colors: ["#DDDF0D", "#55BF3B", "#DF5353", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
      "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
   chart: {
      backgroundColor: {
         linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
         stops: [
            [0, 'rgb(48, 48, 96)'],
            [1, 'rgb(0, 0, 0)']
         ]
      },
      borderColor: '#000000',
      borderWidth: 2,
      className: 'dark-container',
      plotBackgroundColor: 'rgba(255, 255, 255, .1)',
      plotBorderColor: '#CCCCCC',
      plotBorderWidth: 1
   },
   title: {
      style: {
         color: '#C0C0C0',
         font: 'bold 16px "Trebuchet MS", Verdana, sans-serif'
      }
   },
   subtitle: {
      style: {
         color: '#666666',
         font: 'bold 12px "Trebuchet MS", Verdana, sans-serif'
      }
   },
   xAxis: {
      gridLineColor: '#333333',
      gridLineWidth: 1,
      labels: {
         style: {
            color: '#A0A0A0'
         }
      },
      lineColor: '#A0A0A0',
      tickColor: '#A0A0A0',
      title: {
         style: {
            color: '#CCC',
            fontWeight: 'bold',
            fontSize: '12px',
            fontFamily: 'Trebuchet MS, Verdana, sans-serif'

         }
      }
   },
   yAxis: {
      gridLineColor: '#333333',
      labels: {
         style: {
            color: '#A0A0A0'
         }
      },
      lineColor: '#A0A0A0',
      minorTickInterval: null,
      tickColor: '#A0A0A0',
      tickWidth: 1,
      title: {
         style: {
            color: '#CCC',
            fontWeight: 'bold',
            fontSize: '12px',
            fontFamily: 'Trebuchet MS, Verdana, sans-serif'
         }
      }
   },
   tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.75)',
      style: {
         color: '#F0F0F0'
      }
   },
   toolbar: {
      itemStyle: {
         color: 'silver'
      }
   },
   plotOptions: {
      line: {
         dataLabels: {
            color: '#CCC'
         },
         marker: {
            lineColor: '#333'
         }
      },
      spline: {
         marker: {
            lineColor: '#333'
         }
      },
      scatter: {
         marker: {
            lineColor: '#333'
         }
      },
      candlestick: {
         lineColor: 'white'
      }
   },
   legend: {
      itemStyle: {
         font: '9pt Trebuchet MS, Verdana, sans-serif',
         color: '#A0A0A0'
      },
      itemHoverStyle: {
         color: '#FFF'
      },
      itemHiddenStyle: {
         color: '#444'
      }
   },
   credits: {
      style: {
         color: '#666'
      }
   },
   labels: {
      style: {
         color: '#CCC'
      }
   },

   navigation: {
      buttonOptions: {
         symbolStroke: '#DDDDDD',
         hoverSymbolStroke: '#FFFFFF',
         theme: {
            fill: {
               linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
               stops: [
                  [0.4, '#606060'],
                  [0.6, '#333333']
               ]
            },
            stroke: '#000000'
         }
      }
   },

   // scroll charts
   rangeSelector: {
      buttonTheme: {
         fill: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
               [0.4, '#888'],
               [0.6, '#555']
            ]
         },
         stroke: '#000000',
         style: {
            color: '#CCC',
            fontWeight: 'bold'
         },
         states: {
            hover: {
               fill: {
                  linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                  stops: [
                     [0.4, '#BBB'],
                     [0.6, '#888']
                  ]
               },
               stroke: '#000000',
               style: {
                  color: 'white'
               }
            },
            select: {
               fill: {
                  linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                  stops: [
                     [0.1, '#000'],
                     [0.3, '#333']
                  ]
               },
               stroke: '#000000',
               style: {
                  color: 'yellow'
               }
            }
         }
      },
      inputStyle: {
         backgroundColor: '#333',
         color: 'silver'
      },
      labelStyle: {
         color: 'silver'
      }
   },

   navigator: {
      handles: {
         backgroundColor: '#666',
         borderColor: '#AAA'
      },
      outlineColor: '#CCC',
      maskFill: 'rgba(16, 16, 16, 0.5)',
      series: {
         color: '#7798BF',
         lineColor: '#A6C7ED'
      }
   },

   scrollbar: {
      barBackgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
               [0.4, '#888'],
               [0.6, '#555']
            ]
         },
      barBorderColor: '#CCC',
      buttonArrowColor: '#CCC',
      buttonBackgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
               [0.4, '#888'],
               [0.6, '#555']
            ]
         },
      buttonBorderColor: '#CCC',
      rifleColor: '#FFF',
      trackBackgroundColor: {
         linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
         stops: [
            [0, '#000'],
            [1, '#333']
         ]
      },
      trackBorderColor: '#666'
   },

   // special colors for some of the
   legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
   legendBackgroundColorSolid: 'rgb(35, 35, 70)',
   dataLabelsColor: '#444',
   textColor: '#C0C0C0',
   maskColor: 'rgba(255,255,255,0.3)'
};

// Apply the theme
var highchartsOptions = Highcharts.setOptions(Highcharts.theme);

});
   <? } ?>
   </script>
   <link href="./css/neato.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="header">
<h1>MarketDisplay
<span style="width:100px;float:right"><form name="selectserver" id="selectserver" method="GET">Server:<br/>
<select name="server" onchange="this.form.submit()">
<? foreach ($servers as $s) {
   $selected = "";
   if ($s == $server) $selected = ' selected';
   echo '<option value="'.$s.'" '.$selected.'>'.$s.'</option>\n';
}
?></h1>
</select></form></span>
</div>
<div id="main">
<p>Provided by SumRandomTechGuys<br/>MarketDisplay.php version 20130704</p>
<div id="containerFood" style="height: 400px; min-width: 400px; margin:20px 0px 0px 20px; float: left; width: 48%;"></div>
<div id="containerLumber" style="height: 400px; min-width: 400px; margin:20px 0px 0px 20px; float: left; width: 48%;"></div>
<div id="containerStone" style="height: 400px; min-width: 400px; margin:20px 0px 0px 20px; float: left; width: 48%;"></div>
<div id="containerIron" style="height: 400px; min-width: 400px; margin:20px 0px 0px 20px; float: left; width: 48%;"></div>
</div>
<div id="footer">
<p>Visit <a href="http://sumrandomguy.com">SumRandomGuy's website</a></p>
<p>NEATO Version: <?=$NEATO_VERSION?></p>
</div>
<script src="./js/highstock.js"></script>
<script src="./js/modules/exporting.js"></script>
</body>
</html>
