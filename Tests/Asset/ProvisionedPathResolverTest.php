<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\Asset;

use Lolautruche\EzCoreExtraBundle\Asset\AssetPathResolver;
use Lolautruche\EzCoreExtraBundle\Asset\AssetPathResolverInterface;
use Lolautruche\EzCoreExtraBundle\Asset\ProvisionedPathResolver;
use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_TestCase;

class ProvisionedPathResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AssetPathResolverInterface
     */
    private $innerResolver;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $webrootDir;

    protected function setUp()
    {
        parent::setUp();
        $this->innerResolver = $this->createMock(AssetPathResolverInterface::class);
        $this->webrootDir = vfsStream::setup('web');
    }

    public function testResolvePathNotProvisioned()
    {
        $assetLogicalPath = 'images/some_image.jpg';
        $design = 'foo';
        $expected = 'some/path/images/some_image.jpg';
        $this->innerResolver
            ->expects($this->once())
            ->method('resolveAssetPath')
            ->with($assetLogicalPath, $design)
            ->willReturn($expected);

        $resolver = new ProvisionedPathResolver(
            ['bar' => ['images/some_image.jpg' => 'other/path/images/some_image.jpg']],
            $this->innerResolver,
            __DIR__
        );
        self::assertSame($expected, $resolver->resolveAssetPath($assetLogicalPath, $design));
    }

    public function testResolveProvisionedPath()
    {
        $expected = 'some/path/images/some_image.jpg';
        $assetLogicalPath = 'images/some_image.jpg';
        $resolvedPaths = [
            'foo' => [$assetLogicalPath => $expected]
        ];
        $design = 'foo';

        $resolver = new ProvisionedPathResolver($resolvedPaths, $this->innerResolver, __DIR__);
        self::assertSame($expected, $resolver->resolveAssetPath($assetLogicalPath, $design));
    }

    public function testProvisionResolvedPaths()
    {
        $design = 'some_design';
        $themesPaths = [
            'assets',
            'assets/themes/theme1',
            'assets/themes/theme2',
            'assets/themes/theme3',
            'non_existing_directory',
        ];
        $physicalAssets = [
            'assets/themes/theme1/images/foo.png',
            'assets/themes/theme1/images/bar.png',
            'assets/themes/theme1/css/foo.css',
            'assets/themes/theme2/css/foo.css',
            'assets/themes/theme3/images/bar.png',
            'assets/themes/theme3/images/biz.png',
            'assets/js/app.js',
        ];
        $expectedResolvedPaths = [
            'images/foo.png' => 'assets/themes/theme1/images/foo.png',
            'images/bar.png' => 'assets/themes/theme1/images/bar.png',
            'css/foo.css' => 'assets/themes/theme1/css/foo.css',
            'images/biz.png' => 'assets/themes/theme3/images/biz.png',
            'js/app.js' => 'assets/js/app.js',
        ];

        foreach ($physicalAssets as $path) {
            $fileInfo = new \SplFileInfo($path);
            $parent = $this->webrootDir;
            foreach (explode('/', $fileInfo->getPath()) as $dir) {
                if (!$parent->hasChild($dir)) {
                    $directory = vfsStream::newDirectory($dir)->at($parent);
                } else {
                    $directory = $parent->getChild($dir);
                }

                $parent = $directory;
            }

            vfsStream::newFile($fileInfo->getFilename())->at($parent)->setContent('Vive le sucre !!!');
        }

        $innerResolver = new AssetPathResolver([$design => $themesPaths], $this->webrootDir->url());
        $provisioner = new ProvisionedPathResolver([], $innerResolver, $this->webrootDir->url());
        self::assertEquals($expectedResolvedPaths, $provisioner->provisionResolvedPaths($themesPaths, $design));
    }
}
