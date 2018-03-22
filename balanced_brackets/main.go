package main

import (
	"bufio"
	"fmt"
	"os"
	"strconv"
)

var m = map[rune]rune{'(': ')', '{': '}', '[': ']'}

func checkBrackets(stringToCheck string) bool {
	stack := make([]rune, 0, len(stringToCheck))
	var last rune
	for _, char := range stringToCheck {
		if _, ok := m[char]; ok {
			stack = append(stack, char)
		} else {
			if len(stack) == 0 {
				return false
			}
			last, stack = stack[len(stack)-1], stack[:len(stack)-1]
			if char != m[last] {
				return false
			}
		}
	}
	if len(stack) != 0 {
		return false
	}
	return true
}

func main() {

	scn := bufio.NewScanner(os.Stdin)

	fmt.Println("Enter number of lines to parse:")
	var lines []string
	var numberOfThingsToParse int64
	for scn.Scan() {
		line := scn.Text()
		if len(line) == 1 {
			// Group Separator (GS ^]): ctrl-]
			if line[0] == '\x1D' {
				break
			}
		}
		lines = append(lines, line)
		if len(lines) == 1 {
			i, err := strconv.ParseInt(lines[0], 10, 64)
			if err != nil {
				panic(err)
			}
			numberOfThingsToParse = i
		}
		if int64(len(lines)) == numberOfThingsToParse+1 {
			break
		}
	}
	var response []string
	if len(lines) > 0 {
		fmt.Println()
		fmt.Println("Result:")
		for _, line := range lines[1:] {
			valid := checkBrackets(line)
			if valid {
				response = append(response, "YES")
				fmt.Println("YES")
			} else {
				response = append(response, "NO")
				fmt.Println("NO")
			}

		}
		fmt.Println()
	}

	if err := scn.Err(); err != nil {
		fmt.Fprintln(os.Stderr, err)
	}

}
