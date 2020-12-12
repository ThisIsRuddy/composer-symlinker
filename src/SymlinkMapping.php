<?php

namespace ThisIsRuddy\ComposerSymlinker;

/**
 * Class Plugin
 *
 * @author  Dan Watts <thisisruddy@gmail.com>
 *
 * @package ThisIsRuddy\ComposerSymlinker
 */
class SymlinkMapping implements SymlinkMappingInterface
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $dest;

    /**
     * @param array $mapping
     * @throws \Exception
     */
    function __construct(array $mapping)
    {
        if (!array_key_exists(SymlinkMappingInterface::SOURCE_FIELD, $mapping) |
            !array_key_exists(SymlinkMappingInterface::DESTINATION_FIELD, $mapping))
            throw new \Exception(
                sprintf("You must supply a '%s' & '%s' field.",
                    SymlinkMappingInterface::SOURCE_FIELD,
                    SymlinkMappingInterface::DESTINATION_FIELD
                )
            );

        $this->source = $mapping[SymlinkMappingInterface::SOURCE_FIELD];
        $this->dest = $mapping[SymlinkMappingInterface::DESTINATION_FIELD];
    }

    /**
     * @param $path
     * @return false|mixed|string
     */
    static function resolvePath($path)
    {
        if (DIRECTORY_SEPARATOR !== '/')
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $search = explode('/', $path);
        $search = array_filter($search, function ($part) {
            return $part !== '.';
        });

        $append = array();
        $match = false;
        while (count($search) > 0) {
            $match = realpath(implode('/', $search));

            if ($match !== false)
                break;

            array_unshift($append, array_pop($search));
        }

        if ($match === false)
            $match = getcwd();

        if (count($append) > 0)
            $match .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $append);

        return $match;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->dest;
    }

    /**
     * @return string
     */
    public function getSourcePath(): string
    {
        return SymlinkMapping::resolvePath($this->source);
    }

    /**
     * @return string
     */
    public function getDestinationPath(): string
    {
        return SymlinkMapping::resolvePath($this->dest);
    }
}
