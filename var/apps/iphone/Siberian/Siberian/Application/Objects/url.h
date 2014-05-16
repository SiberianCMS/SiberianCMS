//
//  url.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <Foundation/Foundation.h>
#import "common.h"

@interface url : NSObject {
    NSString *scheme;
    NSString *domain;
    NSString *language_code;
    NSString *path;
}

@property (nonatomic, retain) NSString *scheme;
@property (nonatomic, retain) NSString *domain;
@property (nonatomic, retain) NSString *language_code;
@property (nonatomic, retain) NSString *path;

+ (url *)sharedInstance;


- (NSString *)get:(NSString *)uri;
- (NSString *)getImage:(NSString *)path;
- (NSString *)getBase:(NSString *)uri;


@end
