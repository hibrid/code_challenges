package main

import (
	"bufio"
	"fmt"
	"os"
	"strconv"
	"strings"
)

func getPais(numbers []int64, targetSum int64) int64 {
	m := make(map[int64]int64)
	for _, number := range numbers {
		if _, ok := m[number]; !ok {
			m[number] = 0
		}
		m[number] = m[number] + 1
	}

	var twiceCount int64 = 0
	for _, number := range numbers {
		if _, ok := m[targetSum-number]; ok {
			twiceCount += m[targetSum-number]
		}
		if targetSum-m[number] == number {
			twiceCount--
		}
	}
	return twiceCount / 2
}

func main() {

	scn := bufio.NewScanner(os.Stdin)

	fmt.Println("Enter the sum target followed by the numbers space delimited:")
	var targetValue int64
	var wasTargetSet bool
	var numbersAsStrings []string
	for scn.Scan() {
		line := scn.Text()
		if len(line) == 1 {
			// Group Separator (GS ^]): ctrl-]
			if line[0] == '\x1D' {
				break
			}
		}

		if !wasTargetSet {
			i, err := strconv.ParseInt(line, 10, 64)
			if err != nil {
				panic(err)
			}
			targetValue = i
			wasTargetSet = true
		} else {
			numbersAsStrings = strings.Split(line, " ")
			break
		}
	}
	var numbers []int64
	for _, number := range numbersAsStrings {
		i, err := strconv.ParseInt(number, 10, 64)
		if err != nil {
			panic(err)
		}
		numbers = append(numbers, i)
	}

	fmt.Println()

	count := getPais(numbers, targetValue)
	fmt.Printf("Count: %v", count)

	fmt.Println()

	if err := scn.Err(); err != nil {
		fmt.Fprintln(os.Stderr, err)
	}

}
