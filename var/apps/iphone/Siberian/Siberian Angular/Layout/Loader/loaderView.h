//
//  loaderView.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <UIKit/UIKit.h>
#import <QuartzCore/QuartzCore.h>

@protocol loaderView
@optional

- (void)cancelLoader;

@end

@interface loaderView : UIView {
    id <NSObject, loaderView> delegate;
}

@property (retain) id <NSObject, loaderView> delegate;

@property(nonatomic, strong) UIActivityIndicatorView *indicator;
@property(nonatomic, strong) UIButton *btnCancel;

- (bool) isVisible;
- (void) show;
- (void) hide;
- (void) addCancelButton;

- (IBAction)cancel:(id)sender;

@end
