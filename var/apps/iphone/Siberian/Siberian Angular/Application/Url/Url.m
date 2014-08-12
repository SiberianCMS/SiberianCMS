//
//  Url.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "Url.h"

@implementation Url

@synthesize scheme = _scheme, domain = _domain, language_code, path = _path;

static Url *sharedInstance = nil;

- (id)init
{
    self = [super init];
    if (self) {
        
        NSString *stringUrl = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"Default URL"];
        NSURL *defaultUrl = [[NSURL alloc] initWithString:stringUrl];
        sharedInstance = self;
        _scheme = [defaultUrl scheme];
        _domain = [defaultUrl host]; // @"192.168.31.69"; //@"192.168.0.16";
        language_code = @"en";
        _path = [defaultUrl path]; // @"overview";
        
        [self prepareLanguages];
        
        NSString *systemLanguageCode = [[NSLocale preferredLanguages] objectAtIndex:0];
        systemLanguageCode = [[systemLanguageCode componentsSeparatedByString:@"-"] objectAtIndex:0];
        NSString *currentLanguageCode = @"en";

        if([languages containsObject:systemLanguageCode]) {
            currentLanguageCode = systemLanguageCode;
        }
        
        language_code = currentLanguageCode;
        NSLog(@"language_code : %@", language_code);
    }

    return self;
}

- (void)prepareLanguages {
    
    NSURL *url = [[NSURL alloc] initWithString:[self get:@"application/mobile/languages"]];
    NSString *strLanguages = [[NSString alloc] initWithContentsOfURL:url encoding:NSUTF8StringEncoding error:nil];
    languages = [strLanguages componentsSeparatedByString:@","];
}

+ (Url *)sharedInstance {

    if (nil != sharedInstance) {
        return sharedInstance;
    }

    return [[Url alloc] init];
}

- (NSString *)get:(NSString *)uri {
    
    NSString *url = [_scheme stringByAppendingFormat:@"://%@/%@", _domain, _path];
    if(uri.length > 0) {
        url = [url stringByAppendingFormat:@"/%@", uri];
    }
    url = [self addPreviewTo:url];
    return url;
}

- (NSString *)getBase:(NSString *)uri {
    
    NSString *url = [_scheme stringByAppendingFormat:@"://%@", _domain];
    
    if(uri.length > 0) {
        url = [url stringByAppendingFormat:@"/%@", uri];
    }
    
    return url;
    
}

- (NSString *)getImage:(NSString *)imagePath {
    return [_scheme stringByAppendingFormat:@"://%@/%@", _domain, imagePath];
}

- (NSString *)addPreviewTo:url {
//    if([url rangeOfString:@"?"].location == NSNotFound) {
//        url = [url stringByAppendingString:@"?"];
//    } else {
//        url = [url stringByAppendingString:@"&"];
//    }
//    url = [url stringByAppendingString:@"preview=1"];
    
    return url;
}

- (void)setScheme:(NSString *)newScheme {
    _scheme = newScheme;
}
- (void)setDomain:(NSString *)newDomain {
    _domain = [self sanitize:newDomain];
}
- (void)setPath:(NSString *)newPath {
    _path = [self sanitize:newPath];
}


- (NSString *)sanitize:(NSString *)str {
    if([str hasPrefix:@"/"]) {
        str = [str substringFromIndex:1];
    }
    if([str hasSuffix:@"/"]) {
        str = [str substringToIndex:str.length-1];
    }
    return str;
    
}


@end
