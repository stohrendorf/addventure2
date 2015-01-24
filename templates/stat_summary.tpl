{extends 'layout.tpl'}
{block name=headElements}
    <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="{$url.jqplot.excanvas}"></script><![endif]-->
    <script language="javascript" type="text/javascript" src="{$url.jqplot.js}"></script>
    <script language="javascript" type="text/javascript" src="{$url.jqplot.categoryAxisRenderer}"></script>
    <script language="javascript" type="text/javascript" src="{$url.jqplot.canvasAxisTickRenderer}"></script>
    <script language="javascript" type="text/javascript" src="{$url.jqplot.canvasTextRenderer}"></script>
    <script language="javascript" type="text/javascript" src="{$url.jqplot.barRenderer}"></script>
    <link rel="stylesheet" type="text/css" href="{$url.jqplot.css}" />
{/block}

{block name=title}{t}Weekly stats{/t}{/block}

{block name=body}
    <div class="panel panel-info">
        <div class="panel-heading">
            <h4>{t}Summary{/t}</h4>
        </div>
        <div class="panel-body">
            {t 1={$usercount} 2={$episodecount} 3={$firstwritten} 4={$lastwritten}}%1 users have written %2 episodes between %3 and %4.{/t}
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading">
            <h4>{t}Episodes written per week{/t}</h4>
        </div>
        <div id="chart" class="panel-body" style="margin:5px;"></div>
    </div>
    <script type="text/javascript">
        $(function () {
            var plotdata = {$weeklydata};
            var plot = $.jqplot('chart', [plotdata], {
                            series: [{
                                    renderer: $.jqplot.BarRenderer,
                                    rendererOptions: {
                                        barWidth: 2
                                    }
                                }],
                            seriesColors: ['blue'],
                            axes: {
                                xaxis: {
                                    renderer: $.jqplot.CategoryAxisRenderer,
                                    tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                                    tickOptions: {
                                        angle: -60
                                    }
                                },
                                yaxis: {
                                    autoscale: true,
                                    tickRenderer: $.jqplot.CanvasAxisTickRenderer
                                }
                            },
                            highlighter: {
                                showMarker: false,
                                tooltipAxes: 'xy',
                                formatString: '<table class="jqplot-highlighter"> \
                    <tr><td>{t}Date of week{/t}</td><td>%s</td></tr> \
                    <tr><td>{t}Written episodes{/t}</td><td>%s</td></tr> \
                    </table>'
                            }
                        });
                    });
    </script>
{/block}