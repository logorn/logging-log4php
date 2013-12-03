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

namespace Apache\Log4php\Appenders;

use Apache\Log4php\Configurable;
use Apache\Log4php\Filters\AbstractFilter;
use Apache\Log4php\Layouts\SimpleLayout;
use Apache\Log4php\LoggingEvent;

/**
 * Abstract class that defines output logs strategies.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link http://logging.apache.org/log4php
 */
abstract class AbstractAppender extends Configurable
{
    /**
     * Set to true when the appender is closed. A closed appender will not
     * accept any logging requests.
     * @var boolean
     */
    protected $closed = false;

    /**
     * The first filter in the filter chain.
     * @var AbstractFilter
     */
    protected $filter;

    /**
     * The appender's layout. Can be null if the appender does not use
     * a layout.
     * @var Layout
     */
    protected $layout;

    /**
     * Appender name. Used by other components to identify this appender.
     * @var string
     */
    protected $name;

    /**
     * Appender threshold level. Events whose level is below the threshold
     * will not be logged.
     * @var Level
     */
    protected $threshold;

    /**
     * Set to true if the appender requires a layout.
     *
     * True by default, appenders which do not use a layout should override
     * this property to false.
     *
     * @var boolean
     */
    protected $requiresLayout = true;

    /**
     * Default constructor.
     * @param string $name Appender name
     */
    public function __construct($name = '')
    {
        $this->name = $name;

        if ($this->requiresLayout) {
            $this->layout = $this->getDefaultLayout();
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Returns the default layout for this appender. Can be overriden by
     * derived appenders.
     *
     * @return Layout
     */
    public function getDefaultLayout()
    {
        return new SimpleLayout();
    }

    /**
     * Adds a filter to the end of the filter chain.
     * @param AbstractFilter $filter add a new AbstractFilter
     */
    public function addFilter($filter)
    {
        if ($this->filter === null) {
            $this->filter = $filter;
        } else {
            $this->filter->addNext($filter);
        }
    }

    /**
     * Clears the filter chain by removing all the filters in it.
     */
    public function clearFilters()
    {
        $this->filter = null;
    }

    /**
     * Returns the first filter in the filter chain.
     * The return value may be *null* if no is filter is set.
     * @return AbstractFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Returns the first filter in the filter chain.
     * The return value may be *null* if no is filter is set.
     * @return AbstractFilter
     */
    public function getFirstFilter()
    {
        return $this->filter;
    }

    /**
     * Performs threshold checks and invokes filters before delegating logging
     * to the subclass' specific *append*i> method.
     * @see Appender::append()
     * @param LoggingEvent $event
     */
    public function doAppend(LoggingEvent $event)
    {
        if ($this->closed) {
            return;
        }

        if (!$this->isAsSevereAsThreshold($event->getLevel())) {
            return;
        }

        $filter = $this->getFirstFilter();
        while ($filter !== null) {
            switch ($filter->decide($event)) {
                case AbstractFilter::DENY:
                    return;
                case AbstractFilter::ACCEPT:
                    return $this->append($event);
                case AbstractFilter::NEUTRAL:
                    $filter = $filter->getNext();
            }
        }
        $this->append($event);
    }

    /**
     * Sets the appender layout.
     * @param Layout $layout
     */
    public function setLayout($layout)
    {
        if ($this->requiresLayout()) {
            $this->layout = $layout;
        }
    }

    /**
     * Returns the appender layout.
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Configurators call this method to determine if the appender
     * requires a layout.
     *
     * If this method returns *true*, meaning that layout is required,
     * then the configurator will configure a layout using the configuration
     * information at its disposal. If this method returns *false*,
     * meaning that a layout is not required, then layout configuration will be
     * skipped even if there is available layout configuration
     * information at the disposal of the configurator.
     *
     * In the rather exceptional case, where the appender
     * implementation admits a layout but can also work without it, then
     * the appender should return *true*.
     *
     * @return boolean
     */
    public function requiresLayout()
    {
        return $this->requiresLayout;
    }

    /**
     * Retruns the appender name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the appender name.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the appender's threshold level.
     * @return Level
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Sets the appender threshold.
     *
     * @param Level|string $threshold Either a {@link Level}
     *   object or a string equivalent.
     * @see OptionConverter::toLevel()
     */
    public function setThreshold($threshold)
    {
        $this->setLevel('threshold', $threshold);
    }

    /**
     * Checks whether the message level is below the appender's threshold.
     *
     * If there is no threshold set, then the return value is always *true*.
     *
     * @param  Level   $level
     * @return boolean Returns true if level is greater or equal than
     *   threshold, or if the threshold is not set. Otherwise returns false.
     */
    public function isAsSevereAsThreshold($level)
    {
        if ($this->threshold === null) {
            return true;
        }

        return $level->isGreaterOrEqual($this->getThreshold());
    }

    /**
     * Prepares the appender for logging.
     *
     * Derived appenders should override this method if option structure
     * requires it.
     */
    public function activateOptions()
    {
        $this->closed = false;
    }

    /**
     * Forwards the logging event to the destination.
     *
     * Derived appenders should implement this method to perform actual logging.
     *
     * @param LoggingEvent $event
     */
    abstract protected function append(LoggingEvent $event);

    /**
     * Releases any resources allocated by the appender.
     *
     * Derived appenders should override this method to perform proper closing
     * procedures.
     */
    public function close()
    {
        $this->closed = true;
    }

    /** Triggers a warning for this logger with the given message. */
    protected function warn($message)
    {
        $id = get_class($this) . (empty($this->name) ? '' : ":{$this->name}");
        trigger_error("log4php: [$id]: $message", E_USER_WARNING);
    }
}
