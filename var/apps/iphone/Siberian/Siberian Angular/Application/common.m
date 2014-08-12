//
//  common.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//
#import "common.h"

BOOL isScreeniPhone5() {
    CGRect screenBounds = [[UIScreen mainScreen] bounds];
    CGFloat screenScale = [[UIScreen mainScreen] scale];
    return screenBounds.size.height * screenScale >= 1136;
}

BOOL isAtLeastiOS7() { return [[[UIDevice currentDevice] systemVersion] floatValue] >= 7.0; }

UIColor* getBlueColor() {return [UIColor colorWithRed:0.0706 green:0.6549 blue:1 alpha:1];}
UIColor* getWhiteColor() {return [UIColor whiteColor];}

@implementation common

+ (void)replaceTextWithLocalizedTextInSubviewsForView:(UIView*)view
{
    for (UIView* v in view.subviews)
    {
        if (v.subviews.count > 0) {
            [self replaceTextWithLocalizedTextInSubviewsForView:v];
        }
        
        if ([v isKindOfClass:[UILabel class]]) {
            UILabel* l = (UILabel*) v;
            l.text = NSLocalizedString(l.text, nil);
            //            [l sizeToFit];
        }
        else if ([v isKindOfClass:[UIButton class]]) {
            UIButton* b = (UIButton*) v;
            [b setTitle:NSLocalizedString(b.titleLabel.text, nil) forState:UIControlStateNormal];
        }
        else if ([v isKindOfClass:[UITextField class]]) {
            UITextField* tf = (UITextField*) v;
            tf.placeholder = NSLocalizedString(tf.placeholder, nil);
        }
        else if ([v isKindOfClass:[UINavigationBar class]]) {
            UINavigationBar *nb = (UINavigationBar*) v;
            nb.topItem.leftBarButtonItem.title = NSLocalizedString(nb.topItem.leftBarButtonItem.title, nil);
            nb.topItem.rightBarButtonItem.title = NSLocalizedString(nb.topItem.rightBarButtonItem.title, nil);
            nb.topItem.title = NSLocalizedString(nb.topItem.title, nil);
        }
        else if ([v isKindOfClass:[UISegmentedControl class]]) {
            UISegmentedControl *segmentedControl = (UISegmentedControl *) v;
            NSUInteger nbr_of_segments = 0;
            for(nbr_of_segments = 0; nbr_of_segments < segmentedControl.numberOfSegments; nbr_of_segments++) {
                [segmentedControl setTitle:NSLocalizedString([segmentedControl titleForSegmentAtIndex:nbr_of_segments], nil) forSegmentAtIndex:nbr_of_segments];
            }
            
        }
        
    }
}

+ (NSString *)unescape:(NSString *)string {
    return [string stringByReplacingOccurrencesOfString:@"\\" withString:@""];
}

@end
