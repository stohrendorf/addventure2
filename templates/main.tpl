{extends 'layout.tpl'}
{block name=title}
    The Trunk of the Addventure
{/block}
{block name=body}
    <h3>Hello little tree climber!</h3>
    <p>
        <strong>This is the all-new Addventure.</strong>
    </p>
    <p>
        Well... in fact, it is the same Addventure.
        But better: watered, mucked, and polled, so that it is way more pleasant to look at.
        You'll even find some new flowers here and there between the limbs:
        <ul>
            <li>RSS and ATOM feeds of the newest buds.</li>
            <li>Better tools to let new leaves sprout.</li>
            <li>Hearts carved into the wood, telling you how much a branch is loved.</li>
            <li>Some cool smilies <img src="{$url.base}/images/smileys/face-cool.png"/></li>
        </ul>
    </p>
    <h3>Rules for everyone</h3>
    <p>
        <ul>
            <li>Be kind!</li>
            <li>It's easy to break the storyline by introducing a new twist.  Try to write new episodes as if they were a seamless proceeding.</li>
            <li><strong>Don't</strong> overuse <span class="text-warning">formatting</span>.  Think of people who are dressed <span style="color:magenta;">way too fancy</span> and/or have put <em>so much makeup</em> on their faces that they <span style="text-decoration:underline;">look like an oil painting.</span></li>
            <li>You can <em>intend</em> a direction for sequels, but don't try to <em>force</em> it.</li>
            <li>Watch out for you're spelling.  It's distarcting if theirs to much worng.</li>
            <li>DON'T OVERUSE CAPITALS!!! OR PUNCTUATION!!!!!!!</li>
        </ul>
    </p>
    <h3>The roots</h3>
    <p>
        If you want to start reading, you may either <a href="{$url.site}/doc/random">follow the white rabbit</a>, or start at an origin:
        {$combined = array()}
        {foreach $roots as $root}
            {$combined[] = "<a href=\"{$url.site}/doc/{$root.id}\">{$root.title|escape}</a>"}
        {/foreach}
        {$combined|implode:"&nbsp;&nbsp;&nbsp;//&nbsp;&nbsp;&nbsp;"}
    </p>
{/block}
