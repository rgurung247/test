#include <stdio.h>

int main()
{
	printf("This is my first ANSI C program");
    // Add variables and calculations
    int number1 = 10;
    int number2 = 5;
    int sum = number1 + number2;
    
    printf("The sum of %d and %d is: %d\n", number1, number2, sum);
    
    // Add user input
    int userNumber;
    printf("Enter a number: ");
    scanf("%d", &userNumber);
    printf("You entered: %d\n", userNumber);
    
    return 0;
}
