<?php

declare(strict_types = 1);

final class ReturValuesChecker2{}

final class ReturValuesChecker
{
    const NO_RETURN_VALUE = 'void';

    public function scanDir(string $path)
    {
        $it = new RecursiveDirectoryIterator($path);
        $it = new RecursiveIteratorIterator($it);
        $it = new RegexIterator($it, '/\.php$/i');

        /** @var SplFileInfo[] $it */
        foreach ($it as $fi) {
            $classes = $this->getClassesFromFile($fi->getPathname());

            if ($classes === []) {
                continue;
            }

            $reflectionClass = new ReflectionClass($classes[0]);
            $methods         = $reflectionClass->getMethods();

            foreach ($methods as $method) {
                $this->checkIfMethodHasReturType($method);
            }
        }
    }

    private function checkIfMethodHasReturType(ReflectionMethod $method)
    {
        if ($method->getName() === '__construct') {
            return;
        }

        $returnValue = $this->getReturnValue($this->parseMethod($method));

        $reflectionMethod = new ReflectionMethod($method->getDeclaringClass()->getName(), $method->getName());

        if ($returnValue !== self::NO_RETURN_VALUE && $reflectionMethod->getReturnType() === null) {
            trigger_error(sprintf('All public methods in the %s require a return type. Method "%s" returns %s',
                get_class($this), $method->getName(), $returnValue));
        }

    }

    private function parseMethod(ReflectionMethod $method): array
    {
        $file = explode(PHP_EOL, file_get_contents($method->getFileName()));

        $reflectionMethod = new ReflectionMethod($method->getDeclaringClass()->getName(), $method->getName());

        foreach ($file as $key => $row) {
            if ($key < $reflectionMethod->getStartLine() - 1) {
                unset($file[$key]);
                continue;
            }

            if ($key > $reflectionMethod->getEndLine() - 1) {
                unset($file[$key]);
                continue;
            }

            if (strpos($row, '    ') === 0) {
                $file[$key] = substr_replace($row, '', 0, 4);
            }
        }

        return $file;
    }

    private function getReturnValue(array $parsedMethod): string
    {
        foreach ($parsedMethod as $key => $row) {
            if ($pos = strpos(strtolower(ltrim($row)), 'return;') !== false) {
                return self::NO_RETURN_VALUE;
            }

            if ($pos = strpos(strtolower(ltrim($row)), 'return') !== false) {
                $returnValue = substr(ltrim($row), $pos + 6);

                return ltrim($this->stripSemicolon($returnValue));
            }
        }

        if (!isset($returnValue)) {
            return self::NO_RETURN_VALUE;
        }
    }

    private function stripSemicolon($returnValue): string
    {
        if (strpos($returnValue, ';', strlen($returnValue) - 1) !== false) {
            $returnValue = substr($returnValue, 0, strlen($returnValue) - 1);
        }

        return $returnValue;
    }

    private function getClassesFromFile(string $filepath): array
    {
        $classes  = [];
        $tokens = token_get_all(file_get_contents($filepath));

        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_CLASS) {
                $classes[] = $tokens[$i + 2][1];
            }
        }

        return $classes;
    }
}
