{extends 'layout.tpl'}
{block name=title}
    {t}Cache information{/t}
{/block}
{block name=body}
    <h3>{t}Database caching statistics{/t}</h3>
    {function printcacheinfo}
        {if $cache!=null}
            <div class="panel panel-default">
                <div class="panel-heading">
                    {t 1=$cacheName 2=$cacheClass}%1 cache (%2){/t}
                </div>
                <div class="panel-body">
                    {t escape=no 1=$cache.hits|default:"?" 2=$cache.misses|default:"?" 3=$cache.uptime|default:"?" 4=$cache.memory_usage|default:"?"}
                    %1 hits, %2 misses, uptime %3 seconds, %4 bytes used
                    {/t}
                </div>
            </div>
        {else}
            <div class="panel panel-warning">
                <div class="panel-body">
                    {t 1=$cacheName}No %1 cache active{/t}
                </div>
            </div>
        {/if}
    {/function}

    {call printcacheinfo cache=$metadata|default:null cacheClass=$metadataClass|default:null cacheName="Metadata"}
    {call printcacheinfo cache=$query|default:null cacheClass=$queryClass|default:null cacheName="Query"}
    {call printcacheinfo cache=$hydration|default:null cacheClass=$hydrationClass|default:null cacheName="Hydration"}
{/block}
