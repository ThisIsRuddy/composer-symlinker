<?php

namespace ThisIsRuddy\ComposerSymlinker;

/**
 * Interface SymlinkMappingInterface
 * @package ThisIsRuddy\ComposerSymlinker
 */
interface SymlinkMappingInterface
{
    /**
     * Composer.json extra field name
     */
    const SOURCE_FIELD = 'src';

    /**
     * Composer.json extra field name
     */
    const DESTINATION_FIELD = 'dest';

    /**
     * @return string
     */
    public function getSource(): string;

    /**
     * @return string
     */
    public function getDestination(): string;

    /**
     * @return string
     */
    public function getSourcePath(): string;

    /**
     * @return string
     */
    public function getDestinationPath(): string;

}
