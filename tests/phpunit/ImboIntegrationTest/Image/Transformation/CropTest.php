<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboIntegrationTest\Image\Transformation;

use Imbo\Image\Transformation\Crop,
    Imagick;

/**
 * @covers Imbo\Image\Transformation\Crop
 * @group integration
 * @group transformations
 */
class CropTest extends TransformationTests {
    /**
     * {@inheritdoc}
     */
    protected function getTransformation() {
        return new Crop();
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getCropParams() {
        return array(
            'cropped area larger than the image' => array(array('width' => 1000, 'height' => 1000), 665, 463, false),
            'cropped area smaller than the image' => array(array('width' => 100, 'height' => 50), 100, 50, true),
            'cropped area smaller than the image with x and y offset' => array(array('width' => 100, 'height' => 100, 'x' => 600, 'y' => 400), 65, 63, true),
            'center mode' => array(array('mode' => 'center', 'width' => 150, 'height' => 100), 150, 100, true),
            'center-x mode' => array(array('mode' => 'center-x', 'y' => 10, 'width' => 50, 'height' => 40), 50, 40, true),
            'center-y mode' => array(array('mode' => 'center-y', 'x' => 10, 'width' => 50, 'height' => 40), 50, 40, true),
            'center mode with cropped area larger than the image' => array(array('mode' => 'center', 'width' => 1000, 'height' => 1000), 665, 463, false),
        );
    }

    /**
     * @dataProvider getCropParams
     */
    public function testCanCropImages($params, $endWidth, $endHeight, $transformed) {
        $image = $this->getMock('Imbo\Model\Image');
        $image->expects($this->any())->method('getWidth')->will($this->returnValue(665));
        $image->expects($this->any())->method('getHeight')->will($this->returnValue(463));

        if ($transformed) {
            $image->expects($this->once())->method('setWidth')->with($endWidth)->will($this->returnValue($image));
            $image->expects($this->once())->method('setHeight')->with($endHeight)->will($this->returnValue($image));
            $image->expects($this->once())->method('hasBeenTransformed')->with(true)->will($this->returnValue($image));
        }

        $event = $this->getMock('Imbo\EventManager\Event');
        $event->expects($this->at(0))->method('getArgument')->with('image')->will($this->returnValue($image));
        $event->expects($this->at(1))->method('getArgument')->with('params')->will($this->returnValue($params));

        $blob = file_get_contents(FIXTURES_DIR . '/image.png');
        $imagick = new Imagick();
        $imagick->readImageBlob($blob);

        $this->getTransformation()->setImagick($imagick)->transform($event);
    }
}
