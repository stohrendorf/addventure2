{extends 'layout.tpl'}
{block name=title}
    {t}Reports list{/t}
{/block}
{block name=body}
    <div class="panel panel-default">
        <div class="panel-body">
            {if empty($reports)}
                {t}No reports{/t}
            {else}
                <ol start="{$firstIndex+1}">
                    {foreach $reports as $report}
                        <li>
                            [
                                <a href="{$url.site}/maintenance/deletereport/{$report.episode.id}/{$report.type}/{$currentPage}">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </a>
                                {if $report.type == 0}
                                    {t}Rule-break{/t}
                                {elseif $report.type == 1}
                                    {t}Pre-notes{/t}
                                {elseif $report.type == 2}
                                    {t}Post-notes{/t}
                                {elseif $report.type == 3}
                                    {t}Formatting{/t}
                                {/if}
                            ]
                            <a href="{$url.site}/doc/{$report.episode.id}">
                                {$report.episode.autoTitle|escape}
                            </a>
                        </li>
                    {/foreach}
                </ol>
            {/if}
        </div>
        <div class="panel-footer">
            <div style="text-align: center;">
                {$pagination}
            </div>
        </div>
    </div>
{/block}
