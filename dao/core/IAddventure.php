<?php

namespace addventure;

interface IAddventure {
    function toJson();
    function toSmarty();
    function toRss(\SimpleXMLElement &$parent);
    function toAtom(\SimpleXMLElement &$parent);
}
