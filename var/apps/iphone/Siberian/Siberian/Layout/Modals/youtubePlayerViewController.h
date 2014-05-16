//
//  youtubePlayerViewController.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <UIKit/UIKit.h>
#import "loaderView.h"
#import "common.h"

@interface youtubePlayerViewController : UIViewController <UIWebViewDelegate, UIAlertViewDelegate> {
    loaderView *loader;
}

@property (strong, nonatomic) IBOutlet UIWebView *webview;
@property (strong, nonatomic) NSString *videoId;

@end
