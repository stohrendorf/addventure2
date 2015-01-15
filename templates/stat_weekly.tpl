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

{block name=title}Weekly stats{/block}

{block name=body}
    <div id="chart" style="width:100%; margin:10px;"></div>
    <script type="text/javascript">
        $(function () {
            var plotdata = {$plotdata};
            var plot = $.jqplot('chart', [plotdata], {
                title: 'Episodes written per week',
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
                    <tr><td>Date of week</td><td>%s</td></tr> \
                    <tr><td>Written episodes</td><td>%s</td></tr> \
                    </table>'
                }
            });
        });
    </script>
{/block}