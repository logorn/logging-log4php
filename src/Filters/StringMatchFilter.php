<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Apache\Log4php\Filters;

use Apache\Log4php\LoggingEvent;

/**
 * This is a very simple filter based on string matching.
 *
 * The filter admits two options {@link $stringToMatch} and
 * {@link $acceptOnMatch}. If there is a match (using {@link PHP_MANUAL#strpos}
 * between the value of the {@link $stringToMatch} option and the message
 * of the {@link LoggingEvent},
 * then the {@link decide()} method returns {@link AbstractFilter::ACCEPT} if
 * the **AcceptOnMatch** option value is true, if it is false then
 * {@link AbstractFilter::DENY} is returned. If there is no match, {@link AbstractFilter::NEUTRAL}
 * is returned.
 *
 *
 * An example for this filter:
 *
 * {@example ../../examples/php/filter_stringmatch.php 19}
 *
 *
 * The corresponding XML file:
 *
 * {@example ../../examples/resources/filter_stringmatch.xml 18}
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @since 0.3
 */
class StringMatchFilter extends AbstractFilter
{
    /**
     * @var boolean
     */
    protected $acceptOnMatch = true;

    /**
     * @var string
     */
    protected $stringToMatch;

    /**
     * @param mixed $acceptOnMatch a boolean or a string ('true' or 'false')
     */
    public function setAcceptOnMatch($acceptOnMatch)
    {
        $this->setBoolean('acceptOnMatch', $acceptOnMatch);
    }

    /**
     * @param string $s the string to match
     */
    public function setStringToMatch($string)
    {
        $this->setString('stringToMatch', $string);
    }

    /**
     * @return integer a {@link LOGGER_FILTER_NEUTRAL} is there is no string match.
     */
    public function decide(LoggingEvent $event)
    {
        $msg = $event->getRenderedMessage();

        if ($msg === null or $this->stringToMatch === null) {
            return AbstractFilter::NEUTRAL;
        }

        if (strpos($msg, $this->stringToMatch) !== false) {
            return ($this->acceptOnMatch) ? AbstractFilter::ACCEPT : AbstractFilter::DENY;
        }

        return AbstractFilter::NEUTRAL;
    }
}
