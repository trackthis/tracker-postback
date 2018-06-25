<?php

namespace Tracker\Adapter;

use Tracker\Translator\Translator;

abstract class AbstractAdapter implements AdapterInterface {
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @inheritdoc
     */
    public function setFields($mappings) {
        $this->translator = new Translator($mappings);
    }

    /**
     * @inheritdoc
     */
    public function record($bareRecord) {
        return $this->write($this->translator->translate($bareRecord));
    }
}