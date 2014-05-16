//
//  common.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//
#import "common.h"

/**
 * Classe contenant des fonctions communes à toute l'application
 */

BOOL isScreeniPhone5() {
    CGRect screenBounds = [[UIScreen mainScreen] bounds];
    CGFloat screenScale = [[UIScreen mainScreen] scale];
//    CGSize screenSize = CGSizeMake(screenBounds.size.width * screenScale, screenBounds.size.height * screenScale);

    return screenBounds.size.height * screenScale >= 1136;
}

BOOL isAtLeastiOS7() { return [[[UIDevice currentDevice] systemVersion] floatValue] >= 7.0; }

@implementation common

+ (NSString *) unescape:(NSString *)string {
    return [string stringByReplacingOccurrencesOfString:@"\\" withString:@""];
}

@end
