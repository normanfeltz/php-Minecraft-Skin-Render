php Minecraft 3D Skin Renderer
=====================

Render a 3D view of a Minecraft skin using PHP.

Project first developed by <a href="https://github.com/supermamie/php-Minecraft-3D-skin" target="_blank">supermamie</a>. Later transalated to English by <a href="https://github.com/cajogos/php-Minecraft-3D-Skin-Renderer" target="_blank">cajogos</a>.

My goal is to fix some issues and hopefully create full support for the 1.8 skins.

### Example of URL:
`http://example.com/3d.php?vr=-25&hr=-25&hrh=0&vrla=0&vrra=0&vrll=0&vrrl=0&ratio=12&format=png&displayHair=true&headOnly=false&user=cajogos`
Note: The old parameters by supermamie will still work.

### Required parameters
Supermamie's old parameters will still work.

- `user` = Minecraft's username for the skin to be rendered.
- `vr` = Vertical Rotation
- `hr` = Horizontal Rotation
- `hrh` = Horizontal Rotation Head
- `vrll` = Vertical Rotation Left Leg
- `vrrl` = Vertical Rotation Right Leg
- `vrla` = Vertical Rotation Left Arm
- `vrra` = Vertical Rotation Right Arm
- `displayHair` = Either or not to display hairs. Set to "false" to NOT display hairs.
- `headOnly` = Either or not to display the ONLY the head. Set to "true" to display ONLY the head (and the hair, based on displayHair).
- `format` = The format in which the image is to be rendered. PNG ("png") is used by default. Set to "svg" to use a vector version and "base64" for an encoded base64 string of the image.
- `ratio` = The size of the "png" image. The default and minimum value is 2.

### Optional parameters
These parameters can be added to the URL, but are not required.
- `aa` = Anti-aliasing (Not real AA, fake AA). When set to "true" the image will be smoother. "false" by default.

### Changes Made
- Fixed dark blue skins;
- Fixed not working SVG images (Bug in cajogos fork);
- Fixed non-transparent PNG images rendering incorrect (Fix is a bit experimental);
- Made the old parameters by supermamie work again;
- Made 1.8 skins work (still render as the old skin type);
- Added ability to output an encoded base64 string of the image;
- Added optional AA (image smoothing) parameter.
- Made Steve the fallback image.