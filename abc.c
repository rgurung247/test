#include <stdio.h>

int main()
{
    int sum = 0, i = 0;
    file = fopen(filename, "r");
    while ((i = fgetc(file)) != EOF) {
        if (isdigit(i)) {
            sum++;
        }
    }

    printf("Sum of numbers are : %i", sum);
    printf("Hello");
    fclose(file);
    printf("123");
}