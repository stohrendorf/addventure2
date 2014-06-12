<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| SMILEYS
| -------------------------------------------------------------------
| This file contains an array of smileys for use with the emoticon helper.
| Individual images can be used to replace multiple simileys.  For example:
| :-) and :) use the same image replacement.
|
| Please see user guide for more info:
| http://codeigniter.com/user_guide/helpers/smiley_helper.html
|
*/

// This code assumes that all smiley images have a size of 22x22

$smileys = array();
$oxygen_smileys = array(
    // Code / Image basename / Image ALT-tag
    array(':-)', 'face-smile', 'Smile'),
    array(':)', 'face-smile-grin', 'Grin'),
    array(':-D', 'face-smile-big', 'Big Smile'),
    array(':D', 'face-smile-big', 'Big Smile'),
    array(';-)', 'face-wink', 'Wink'),
    array(';)', 'face-wink', 'Wink'),
    array(':-|', 'face-smile-plain', 'Plain Smile'),
    array(':|', 'face-smile-plain', 'Plain Smile'),
    array(':-/', 'face-uncertain', 'Uncertain'),
    array(':-(', 'face-sad', 'Sad'),
    array(':(', 'face-sad', 'Sad'),
    array(':-*', 'face-kiss', 'Kiss'),
    array(':-O', 'face-surprise', 'Surprise'),
    array(':O', 'face-surprise', 'Surprise'),
    array(':-P', 'face-raspberry', 'Tongue Wink'),
    array(':P', 'face-raspberry', 'Tongue Wink'),
    array('>:(', 'face-angry', 'Angry'),
    array('>:-(', 'face-angry', 'Angry'),
    array(":'-(", 'face-crying', 'Crying'),
    array(":'(", 'face-crying', 'Crying'),
    array(':-X', 'face-quiet', 'Quiet'),
    array(':X', 'face-quiet', 'Quiet'),
    array(':-#', 'face-quiet', 'Quiet'),
    array(':#', 'face-quiet', 'Quiet'),

    array('*_*', 'face-in-love', 'In Love'),
    array('*-*', 'face-in-love', 'In Love'),
    array('<3', 'heart', 'Heart'),
    
    array(':lol:', 'face-laugh', 'LOL'),
    array(':+1:', 'opinion-agree', 'Agree'),
    array(':agree:', 'opinion-agree', 'Agree'),
    array(':-1:', 'opinion-disagree', 'Disagree'),
    array(':disagree:', 'opinion-disagree', 'Disagree'),
    array(':sick:', 'face-sick', 'Sick'),
    array(':ninja:', 'face-ninja', 'Ninja'),
    array(':pirate:', 'face-pirate', 'Pirate'),
    array(':devil:', 'face-angry', 'Devil'),
    array(':angel:', 'face-angel', 'Angel'),
    array(':beer:', 'drink-beer', 'Beer'),
    array(':martini:', 'drink-martini', 'Martini')
);
foreach($oxygen_smileys as $oxygen_smiley) {
    $smileys[$oxygen_smiley[0]] = array(
        $oxygen_smiley[1] . '.png', // Filename
        '22', // width
        '22', // height
        $oxygen_smiley[2] // ALT tag
    );
}
