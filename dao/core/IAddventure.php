<?php

/**
 * Common interface meant to be implemented by DAO classes with data needed in
 * for views.
 */

namespace addventure;

/**
 * The common transformation view.
 */
interface IAddventure {
    /**
     * Convert the data to a format usable by AJAX requests.
     * @return array JSON data
     */
    function toJson();
    /**
     * Convert the data to a format usable in templates.
     * @return array Smarty data
     */
    function toSmarty();
    /**
     * Convert the data to a format usable in RSS feeds.
     * @return array RSS data
     */
    function toRss(\SimpleXMLElement &$parent);
    /**
     * Convert the data to a format usable in ATOM feeds.
     * @return array ATOM data
     */
    function toAtom(\SimpleXMLElement &$parent);
}
