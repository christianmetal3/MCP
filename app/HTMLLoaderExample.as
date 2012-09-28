package {
    import flash.display.Sprite;
    import flash.html.HTMLLoader;
    import flash.net.URLRequest;

    public class HTMLLoaderExample extends Sprite
    {
        public function HTMLLoaderExample()
        {
            var html:HTMLLoader = new HTMLLoader();
            var urlReq:URLRequest = new URLRequest("");
            html.width = stage.stageWidth;
            html.height = stage.stageHeight;
            html.load(urlReq); 
            addChild(html);
        }
    }
}