//
//  url.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <Foundation/Foundation.h>

@interface Url : NSObject {
    NSString *scheme;
    NSString *domain;
    NSString *language_code;
    NSString *path;
    NSArray *languages;
}

@property (nonatomic, retain) NSString *scheme;
@property (nonatomic, retain) NSString *domain;
@property (nonatomic, retain) NSString *language_code;
@property (nonatomic, retain) NSString *path;

+ (Url *)sharedInstance;


- (NSString *)get:(NSString *)uri;
- (NSString *)getImage:(NSString *)path;
- (NSString *)getBase:(NSString *)uri;
//- (void)setScheme:(NSString *)newScheme;
//- (void)setDomain:(NSString *)newDomain;
//- (void)setPath:(NSString *)newPath;

@end
