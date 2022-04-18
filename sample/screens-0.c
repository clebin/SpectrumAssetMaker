#define SCREENS_LEN 3


typedef struct Enemy {
    char[8] type;
    char[8] movement;
    uint8_t x;
    uint8_t y;
    uint8_t lethal; // boolean
    uint8_t transient; // boolean
    uint8_t endval;
    uint8_t numhits;
    uint8_t speed;
};


typedef struct GameObject {
    char[12] name;
    char[8] type;
    uint8_t row;
    uint8_t col;
    uint8_t lethal; // boolean
    uint8_t collectable; // boolean
    uint8_t numhits;
};

const unsigned char ScreenTiles0[768] = {
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x31,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x32,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x8,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x31,0x0,
0x0,0x0,0x0,0x0,0x0,0x32,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x19,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x19,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x2,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x2,0x0,0x0,
0x0,0x20,0x1e,0x1e,0x1e,0x1e,0x1e,0x1e,
0x1e,0x2,0x1f,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x20,0x1e,0x1e,
0x1e,0x1e,0x1e,0x1e,0x1e,0x2,0x1f,0x0,
0x0,0x1d,0x1b,0x1b,0x1b,0x1b,0x1b,0x1b,
0x1b,0x3c,0x1c,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x1d,0x1b,0x1b,
0x1b,0x1b,0x1b,0x1b,0x1b,0x3c,0x1c,0x0,
0x0,0x1d,0x1b,0x1b,0x1b,0x1b,0x1b,0x1b,
0x1b,0x1b,0x1c,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x0,0x0,0x1d,0x1b,0x1b,
0x1b,0x1b,0x1b,0x1b,0x1b,0x1b,0x1c,0x0,
0x0,0x3f,0x3d,0x3d,0x3d,0x3d,0x3d,0x3d,
0x3d,0x3d,0x3e,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x46,0x0,0x3f,0x3d,0x3d,
0x3d,0x3d,0x3d,0x3d,0x3d,0x3d,0x3e,0x0,
0x0,0x12,0x12,0x12,0x12,0x12,0x12,0x12,
0x12,0x12,0x12,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x43,0x48,0x49,0x12,0x12,0x12,
0x12,0x12,0x12,0x12,0x12,0x12,0x12,0x0,
0x0,0x11,0x10,0x62,0x63,0x10,0x10,0x10,
0x62,0x63,0x10,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x47,0x49,0x48,0x11,0x10,0x6c,
0x6d,0x10,0x10,0x10,0x62,0x63,0x10,0x0,
0x0,0x11,0x10,0x60,0x61,0x10,0x10,0x10,
0x60,0x61,0x10,0x0,0x0,0x0,0x0,0x31,
0x0,0x0,0x4b,0x4a,0x4c,0x11,0x10,0x6a,
0x6b,0x10,0x10,0x10,0x60,0x61,0x10,0x0,
0x0,0x11,0x10,0x10,0x10,0x10,0x10,0x10,
0x10,0x10,0x10,0x0,0x0,0x0,0x0,0x0,
0x0,0x0,0x0,0x37,0x0,0x11,0x10,0x10,
0x10,0x10,0x10,0x10,0x10,0x10,0x10,0x0,
0x2b,0x11,0x10,0x65,0x67,0x69,0x10,0x10,
0x55,0x52,0x10,0x2b,0x2c,0x40,0x41,0x41,
0x42,0x2d,0x2b,0x37,0x2b,0x11,0x10,0x65,
0x67,0x69,0x10,0x10,0x55,0x52,0x10,0x2b,
0x29,0x11,0x10,0x64,0x66,0x68,0x10,0x10,
0x54,0x51,0x10,0x29,0x2f,0x7,0x7,0x7,
0x7,0x2e,0x29,0x37,0x29,0x11,0x10,0x64,
0x66,0x68,0x10,0x10,0x54,0x51,0x10,0x29,
0x29,0x11,0x10,0x10,0x10,0x10,0x10,0x10,
0x53,0x50,0x10,0x29,0x2f,0x7,0x7,0x7,
0x7,0x2e,0x29,0x2a,0x2a,0x11,0x10,0x10,
0x10,0x10,0x10,0x10,0x53,0x50,0x10,0x29,
0x21,0x21,0x21,0x21,0x21,0x21,0x21,0x21,
0x21,0x21,0x21,0x21,0x22,0x7,0x7,0x7,
0x7,0x23,0x21,0x21,0x21,0x21,0x21,0x21,
0x21,0x21,0x21,0x21,0x21,0x21,0x21,0x21,
0x38,0x38,0x38,0x38,0x38,0x38,0x38,0x38,
0x38,0x38,0x38,0x38,0x38,0x7,0x7,0x7,
0x7,0x38,0x38,0x38,0x38,0x38,0x38,0x38,
0x38,0x38,0x38,0x38,0x38,0x38,0x38,0x38,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x25,0x25,0x7,0x7,0x7,0x7,0x7,0x25,
0x25,0x25,0x25,0x25,0x7,0x7,0x7,0x7,
0x7,0x25,0x25,0x25,0x25,0x25,0x7,0x7,
0x7,0x7,0x7,0x25,0x25,0x25,0x25,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7,
0x7,0x7,0x7,0x7,0x7,0x7,0x7,0x7
};


