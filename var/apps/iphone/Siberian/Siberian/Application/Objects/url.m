//
//  url.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "url.h"

@implementation url

@synthesize scheme, domain, language_code, path;

static url *sharedInstance = nil;

- (id)init
{
    self = [super init];
    if (self) {
        sharedInstance = self;
        scheme = @"http";
        domain = @"192.168.0.15";
        language_code = @"en";
        path = @"overview";
        
        NSString *systemLanguageCode = [[NSLocale preferredLanguages] objectAtIndex:0];
        systemLanguageCode = [[systemLanguageCode componentsSeparatedByString:@"-"] objectAtIndex:0];
        NSArray *allowedLanguageCodes = [[NSArray alloc] initWithObjects:@"en", @"fr", @"pt", @"es", @"tr", nil];
        NSString *currentLanguageCode = @"en";

        if([allowedLanguageCodes containsObject:systemLanguageCode]) {
            currentLanguageCode = systemLanguageCode;
        }
        language_code = currentLanguageCode;
        
    }

    return self;
}

+ (url *)sharedInstance {

    if (nil != sharedInstance) {
        return sharedInstance;
    }

    return [[url alloc] init];
}

- (NSString *)get:(NSString *)uri {
    NSString *url = [scheme stringByAppendingFormat:@"://%@/%@", domain, path];
    if(uri.length > 0) {
        url = [url stringByAppendingFormat:@"/%@", uri];
    }
    
    return url;
}

- (NSString *)getBase:(NSString *)uri {
    
    NSString *url = [scheme stringByAppendingFormat:@"://%@", domain];
    
    if(uri.length > 0) {
        url = [url stringByAppendingFormat:@"/%@", uri];
    }
    
    return url;
    
}

- (NSString *)getImage:(NSString *)imagePath {
    return [scheme stringByAppendingFormat:@"://%@/%@", domain, imagePath];
}


@end
