{extends 'common_episodelist.tpl'}
{block name=title}
    {t escape=no 1={$storyline.title|escape}}Episodes with storyline tag &raquo;%1&laquo;{/t}
{/block}

{block name="bodyheader"}
    <div class="panel panel-info">
        <div class="panel-heading">
            <h4>
                {t escape=no count=$episodeCount 1=$episodeCount 2={$storyline.title|escape} plural="%1 Episodes with storyline tag &raquo;%2&laquo;"}%1 Episode with storyline tag &raquo;%2&laquo;{/t}
            </h4>
        </div>
    </div>
{/block}