{extends 'layout.tpl'}
{block name=title}
    {t 1={$targetEpisode}}The chain up to Episode %1{/t}
{/block}

{block name=body}
    {foreach $episodes as $episode}
        <div class="panel panel-success">
            <div class="panel-heading">
                {if isset($episode.chosen)}
                    <span class="glyphicon glyphicon-share-alt"></span>
                    <em>{$episode.chosen}</em>
                    <br/>
                {/if}
                <h3 style="display:inline;" class="panel-title">
                    <a data-toggle="tooltip" data-placement="left" title="{t}Explore from here.{/t}" href="{$url.site}/doc/{$episode.id}">
                        {$episode.autoTitle}
                    </a>
                    {if isset($episode.author) or isset($episode.created)}
                        <span class="text-info">
                            {if isset($episode.author)} {t}by{/t} <a href="{$url.site}/user/{$episode.author.user}">{$episode.author.name}</a>{/if}
                            {if isset($episode.created)} @ {$episode.created}{/if}
                        </span>
                    {/if}
                </h3>
                <a href="{$url.site}/maintenance/illegal/{$episode.id}" style="color:red;" class="pull-right" id="report"
                   data-toggle="tooltip" data-placement="right" title="{t}This function is for reporting content that breaks the rules, not for crying about a bad story.{/t}"> <span class="glyphicon glyphicon-fire"></span> {t}Report inappropriate content{/t}</a>
                <span class="clearfix"></span>
            </div>
            <div class="panel-body">
                <span class="glyphicon glyphicon-eye-open"></span> {$episode.hitcount}
                <span class="glyphicon glyphicon-heart"></span> {$episode.likes}
                <span class="glyphicon glyphicon-heart-empty"></span> {$episode.dislikes}
                <div class="pull-right">
                    {t escape=no}What do <em>you</em> think?{/t}
                    <a href="{$url.site}/like/{$episode.id}"> <span class="glyphicon glyphicon-heart"></span> {t}I like it!{/t}</a>
                    <a href="{$url.site}/dislike/{$episode.id}"> <span class="glyphicon glyphicon-heart-empty"></span> {t}Na, could have been better...{/t}</a>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        {call name="showEpisode"}
    {/foreach}
    <script type="text/javascript">
        $('a[title!=\'\']').each(function(i, e) {
            $(e).tooltip();
        });
    </script>
{/block}
