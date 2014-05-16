//
//  Request.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <Foundation/Foundation.h>
#import "common.h"
#import "url.h"

@protocol Request

- (void) connectionDidFinish:(NSData *)datas;

@optional

- (void) connectionDidFail;

@end

@interface Request : NSObject {
    id <NSObject, Request> delegate;
    bool isSynchronious;
    
    NSMutableData *webData;
}

@property (retain) id <NSObject, Request> delegate;
@property (readwrite) bool isSynchronious;

@property (nonatomic, retain) NSMutableData *webData;

- (void)postDatas:(NSMutableDictionary *)datas withUrl:(NSString *)url;
- (void)postWithUrl:(NSString *)withUrl;

- (void)loadImage:(NSString *)withUrl;

@end
