<?php

/**
 * MetaTune - The ultimate PHP Wrapper to the Spotify Metadata API
 *
 * Copyright (C) 2010  Mikael Brevik
 *
 * <pre>
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see {@link http://www.gnu.org/licenses/}.
 * </pre>
 *
 * Extention of the SimpleXMLElement class to make SimpleXMLElement ´
 * able to add another XML Element as a child to another XML Element.
 */
class MBSimpleXMLElement extends SimpleXMLElement {

    public function addXMLElement(SimpleXMLElement $source) {
        $new_dest = $this->addCData($source->getName(), $source[0]);

        foreach ($source->attributes() as $name => $value) {
            $new_dest->addAttribute($name, $value);
        }

        foreach ($source->children() as $child) {
            $new_dest->addXMLElement($child);
        }
    }

    public function addCData($nodename, $cdata_text) {
        $node2 = $this->addChild($nodename); //Added a nodename to create inside the function
        $node = dom_import_simplexml($node2);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
        return $node2;
    }

}
