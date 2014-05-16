//
//  youtubePlayerViewController.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "youtubePlayerViewController.h"

@interface youtubePlayerViewController ()

@end

@implementation youtubePlayerViewController
@synthesize webview, videoId;

- (void)viewDidLoad {
    
    loader = [[loaderView alloc] initWithFrame:CGRectMake(0, 0, self.view.frame.size.width, self.view.frame.size.height)];
    
    [self.view addSubview:loader];
    [self.view bringSubviewToFront:loader];
    [loader show];
    
    webview.delegate = self;
    
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(playerWillExitFullscreen:)
                                                 name:@"UIMoviePlayerControllerWillExitFullscreenNotification"
                                               object:nil];
    NSString* embedHTML = @"\
    <!DOCTYPE html>\
    <html>\
        <body style=\"background-color:black;\">\
            <div style=\"position:absolute; top:1000px; left:0px; right:0px\" <div id=\"player\"></div></div>\
            <script>\
                var tag = document.createElement('script');\
                tag.src = \"https://www.youtube.com/iframe_api\";\
                var firstScriptTag = document.getElementsByTagName('script')[0];\
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);\
                var player;\
                function onYouTubeIframeAPIReady() {\
                    player = new YT.Player('player', {\
                        height: '%0.0f',\
                        width: '%0.0f',\
                        videoId: '%@',\
                        events: {\
                            'onReady': function(e) {\
                                e.target.playVideo();\
                                window.location = \"app:hideLoader\";\
                            },\
                            onError: function(e) {\
                                window.location = \"app:sendError\";\
                            }\
                        }\
                    });\
                }\
            </script>\
        </body>\
    </html>";
    
    NSString* html = [NSString stringWithFormat:embedHTML, webview.frame.size.width, webview.frame.size.height, videoId];
    webview.mediaPlaybackRequiresUserAction = NO;
    [webview loadHTMLString:html baseURL:[[NSBundle mainBundle] resourceURL]];
    
    [super viewDidLoad];
    
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma clang diagnostic ignored "-Warc-performSelector-leaks"
- (BOOL)webView:(UIWebView *)webView shouldStartLoadWithRequest:(NSURLRequest *)request navigationType:(UIWebViewNavigationType)navigationType {
    
    NSString *stringUrl = [NSString stringWithFormat:@"%@", [request URL]];
    if([stringUrl rangeOfString:@"app:"].location != NSNotFound) {
        NSArray *words = [stringUrl componentsSeparatedByString:@":"];
        SEL function = NSSelectorFromString([words lastObject]);
        if([self respondsToSelector:function]) {
            [self performSelector:function];
        }
        
        return NO;
    }
    
    return YES;
}

- (void)playerWillExitFullscreen:(NSNotification *)notification {
    [self performSelector:@selector(closeYoutube) withObject:nil afterDelay:0.0f];
}

- (void)closeYoutube {
    [self dismissModalViewControllerAnimated:YES];
}

- (void)webView:(UIWebView *)webView didFailLoadWithError:(NSError *)error {
    [self sendError];
}

- (void)sendError {
    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:NSLocalizedString(@"Error", nil) message:NSLocalizedString(@"The video you're requesting is not valid or doesn't exist anymore", nil) delegate:self cancelButtonTitle:@"OK" otherButtonTitles:nil];
    [alert show];
}

- (void)hideLoader {
    [loader hide];
}


- (void)viewDidUnload {
    [self setWebview:nil];
    [super viewDidUnload];
}

- (void)alertView:(UIAlertView *)alertView didDismissWithButtonIndex:(NSInteger)buttonIndex {
    [self closeYoutube];
}

@end
