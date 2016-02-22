<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Oms\Business\OrderStateMachine;

use Spryker\Zed\Oms\Business\Process\EventInterface;
use Spryker\Zed\Oms\Business\Process\StateInterface;
use Spryker\Zed\Oms\Business\Process\TransitionInterface;
use SimpleXMLElement;
use LogicException;

class Builder implements BuilderInterface
{

    /**
     * @var \SimpleXMLElement
     */
    protected $rootElement;

    /**
     * @var \Spryker\Zed\Oms\Business\Process\ProcessInterface[]
     */
    protected static $processBuffer = [];

    /**
     * @var \Spryker\Zed\Oms\Business\Process\EventInterface
     */
    protected $event;

    /**
     * @var \Spryker\Zed\Oms\Business\Process\StateInterface
     */
    protected $state;

    /**
     * @var \Spryker\Zed\Oms\Business\Process\TransitionInterface
     */
    protected $transition;

    /**
     * @var \Spryker\Zed\Oms\Business\Process\ProcessInterface
     */
    protected $process;

    /**
     * @var string
     */
    protected $xmlFolder;

    /**
     * @param \Spryker\Zed\Oms\Business\Process\EventInterface $event
     * @param \Spryker\Zed\Oms\Business\Process\StateInterface $state
     * @param \Spryker\Zed\Oms\Business\Process\TransitionInterface $transition
     * @param \Spryker\Zed\Oms\Business\Process\ProcessInterface $process
     * @param string|null $xmlFolder
     */
    public function __construct(EventInterface $event, StateInterface $state, TransitionInterface $transition, $process, $xmlFolder = null)
    {
        $this->event = $event;
        $this->state = $state;
        $this->transition = $transition;
        $this->process = $process;
        if ($xmlFolder) {
            $this->xmlFolder = $xmlFolder;
        } else {
            // TODO core-122 move to settings
            $this->xmlFolder = APPLICATION_ROOT_DIR . '/config/Zed/oms/';
        }
    }

    /**
     * @param string $processName
     *
     * @return \Spryker\Zed\Oms\Business\Process\ProcessInterface
     */
    public function createProcess($processName)
    {
        if (!isset(self::$processBuffer[$processName])) {
            $this->rootElement = $this->loadXmlFromProcessName($processName);

            $this->mergeSubProcessFiles();

            /** @var \Spryker\Zed\Oms\Business\Process\ProcessInterface[] $processMap */
            $processMap = [];

            list($processMap, $mainProcess) = $this->createSubProcess($processMap);

            $stateToProcessMap = $this->createStates($processMap);

            $this->createSubProcesses($processMap);

            $eventMap = $this->createEvents();

            $this->createTransitions($stateToProcessMap, $processMap, $eventMap);

            assert('isset($mainProcess)');

            self::$processBuffer[$processName] = $mainProcess;
        }

        return self::$processBuffer[$processName];
    }

    /**
     * @return void
     */
    protected function mergeSubProcessFiles()
    {
        foreach ($this->rootElement->children() as $xmlProcess) {
            $processFile = $this->getAttributeString($xmlProcess, 'file');
            if (isset($processFile)) {
                $xmlSubProcess = $this->loadXmlFromFileName(str_replace(' ', '_', $processFile));
                $this->recursiveMerge($xmlSubProcess, $this->rootElement);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $fromXmlElement
     * @param \SimpleXMLElement $intoXmlNode
     *
     * @return void
     */
    protected function recursiveMerge($fromXmlElement, $intoXmlNode)
    {
        $xmlElements = $fromXmlElement->children();
        if (!isset($xmlElements)) {
            return;
        }

        /** @var \SimpleXMLElement $xmlElement */
        foreach ($xmlElements as $xmlElement) {
            $child = $intoXmlNode->addChild($xmlElement->getName(), $xmlElement);
            $attributes = $xmlElement->attributes();
            foreach ($attributes as $k => $v) {
                $child->addAttribute($k, $v);
            }

            $this->recursiveMerge($xmlElement, $child);
        }
    }

    /**
     * @param string $fileName
     *
     * @return \SimpleXMLElement
     */
    protected function loadXmlFromFileName($fileName)
    {
        $xml = file_get_contents($this->xmlFolder . $fileName);

        return $this->loadXml($xml);
    }

    /**
     * @param string $processName
     *
     * @return \SimpleXMLElement
     */
    protected function loadXmlFromProcessName($processName)
    {
        return $this->loadXmlFromFileName($processName . '.xml');
    }

    /**
     * @param string $xml
     *
     * @return \SimpleXMLElement
     */
    protected function loadXml($xml)
    {
        return new SimpleXMLElement($xml);
    }

    /**
     * @return array
     */
    protected function createEvents()
    {
        $eventMap = [];

        foreach ($this->rootElement as $xmlProcess) {
            if (!isset($xmlProcess->events)) {
                continue;
            }

            $xmlEvents = $xmlProcess->events->children();
            foreach ($xmlEvents as $xmlEvent) {
                $event = clone $this->event;
                $eventId = $this->getAttributeString($xmlEvent, 'name');
                $event->setCommand($this->getAttributeString($xmlEvent, 'command'));
                $event->setManual($this->getAttributeBoolean($xmlEvent, 'manual'));
                $event->setOnEnter($this->getAttributeBoolean($xmlEvent, 'onEnter'));
                $event->setTimeout($this->getAttributeString($xmlEvent, 'timeout'));
                if ($eventId === null) {
                    continue;
                }

                $event->setName($eventId);
                $eventMap[$event->getName()] = $event;
            }
        }

        return $eventMap;
    }

    /**
     * @param \Spryker\Zed\Oms\Business\Process\ProcessInterface[] $processMap
     *
     * @return array
     */
    protected function createSubProcess(array $processMap)
    {
        $mainProcess = null;
        $xmlProcesses = $this->rootElement->children();

        /** @var \SimpleXMLElement $xmlProcess */
        foreach ($xmlProcesses as $xmlProcess) {
            $process = clone $this->process;
            $processName = $this->getAttributeString($xmlProcess, 'name');
            $process->setName($processName);
            $processMap[$processName] = $process;
            $process->setMain($this->getAttributeBoolean($xmlProcess, 'main'));

            $process->setFile($this->getAttributeString($xmlProcess, 'file'));

            if ($process->getMain()) {
                $mainProcess = $process;
            }
        }

        return [$processMap, $mainProcess];
    }

    /**
     * @param \Spryker\Zed\Oms\Business\Process\ProcessInterface[] $processMap
     *
     * @return void
     */
    protected function createSubProcesses(array $processMap)
    {
        foreach ($this->rootElement as $xmlProcess) {
            $processName = $this->getAttributeString($xmlProcess, 'name');

            $process = $processMap[$processName];

            if (!empty($xmlProcess->subprocesses)) {
                $xmlSubProcesses = $xmlProcess->subprocesses->children();

                foreach ($xmlSubProcesses as $xmlSubProcess) {
                    $subProcessName = (string)$xmlSubProcess;
                    $subProcess = $processMap[$subProcessName];
                    $process->addSubProcess($subProcess);
                }
            }
        }
    }

    /**
     * @param \Spryker\Zed\Oms\Business\Process\ProcessInterface[] $processMap
     *
     * @return \Spryker\Zed\Oms\Business\Process\ProcessInterface[]
     */
    protected function createStates(array $processMap)
    {
        $stateToProcessMap = [];

        $xmlProcesses = $this->rootElement->children();
        foreach ($xmlProcesses as $xmlProcess) {
            $processName = $this->getAttributeString($xmlProcess, 'name');
            $process = $processMap[$processName];

            if (!empty($xmlProcess->states)) {
                $xmlStates = $xmlProcess->states->children();
                /** @var \SimpleXMLElement $xmlState */
                foreach ($xmlStates as $xmlState) {
                    $state = clone $this->state;
                    $state->setName($this->getAttributeString($xmlState, 'name'));
                    $state->setDisplay($this->getAttributeString($xmlState, 'display'));
                    $state->setReserved($this->getAttributeBoolean($xmlState, 'reserved'));
                    $state->setProcess($process);

                    if ($xmlState->flag) {
                        $flags = $xmlState->children();
                        foreach ($flags->flag as $flag) {
                            $state->addFlag((string)$flag);
                        }
                    }

                    $process->addState($state);
                    $stateToProcessMap[$state->getName()] = $process;
                }
            }
        }

        return $stateToProcessMap;
    }

    /**
     * @param \Spryker\Zed\Oms\Business\Process\ProcessInterface[] $stateToProcessMap
     * @param \Spryker\Zed\Oms\Business\Process\ProcessInterface[] $processMap
     * @param \Spryker\Zed\Oms\Business\Process\EventInterface[] $eventMap
     *
     * @throws \LogicException
     *
     * @return void
     */
    protected function createTransitions(array $stateToProcessMap, array $processMap, array $eventMap)
    {
        foreach ($this->rootElement as $xmlProcess) {
            if (!empty($xmlProcess->transitions)) {
                $xmlTransitions = $xmlProcess->transitions->children();

                $processName = $this->getAttributeString($xmlProcess, 'name');

                foreach ($xmlTransitions as $xmlTransition) {
                    $transition = clone $this->transition;

                    $transition->setCondition($this->getAttributeString($xmlTransition, 'condition'));

                    $transition->setHappy($this->getAttributeBoolean($xmlTransition, 'happy'));

                    $sourceName = (string)$xmlTransition->source;
                    $sourceProcess = $stateToProcessMap[$sourceName];
                    $sourceState = $sourceProcess->getState($sourceName);
                    $transition->setSource($sourceState);
                    $sourceState->addOutgoingTransition($transition);

                    $targetName = (string)$xmlTransition->target;

                    if (!isset($stateToProcessMap[$targetName])) {
                        throw new LogicException('Target: "' . $targetName . '" does not exist from source: "' . $sourceName . '"');
                    }
                    $targetProcess = $stateToProcessMap[$targetName];
                    $targetState = $targetProcess->getState($targetName);
                    $transition->setTarget($targetState);
                    $targetState->addIncomingTransition($transition);

                    if (isset($xmlTransition->event)) {
                        $eventId = (string)$xmlTransition->event;

                        if (!isset($eventMap[$eventId])) {
                            throw new LogicException('Event: "' . $eventId . '" does not exist from source: "' . $sourceName . '"');
                        }

                        $event = $eventMap[$eventId];
                        $event->addTransition($transition);
                        $transition->setEvent($event);
                    }

                    $processMap[$processName]->addTransition($transition);
                }
            }
        }
    }

    /**
     * @param \SimpleXMLElement $xmlElement
     * @param string $attributeName
     *
     * @return string
     */
    protected function getAttributeString(SimpleXMLElement $xmlElement, $attributeName)
    {
        $string = (string)$xmlElement->attributes()[$attributeName];
        $string = ($string === '') ? null : $string;

        return $string;
    }

    /**
     * @param \SimpleXMLElement $xmlElement
     * @param string $attributeName
     *
     * @return bool
     */
    protected function getAttributeBoolean(SimpleXMLElement $xmlElement, $attributeName)
    {
        return (string)$xmlElement->attributes()[$attributeName] === 'true';
    }

}
