package main

import (
	"bufio"
	"fmt"
	"os"
	"strconv"
	"strings"
)

func getPais(numbers []int32, targetSumOriginal int64) int32 {
	m := make(map[int32]int32)
	targetSum := int32(targetSumOriginal)
	for _, number := range numbers {
		if _, ok := m[number]; !ok {
			m[number] = 1
		} else {
			m[number] = m[number] + 1
		}

	}

	var twiceCount int32 = 0

	for _, number := range numbers {
		if _, ok := m[targetSum-number]; ok {
			if targetSum-number == number && m[number] == 1 {
				continue
			}
			twiceCount++
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
	var numbers []int32
	for _, number := range numbersAsStrings {
		i, err := strconv.Atoi(number)
		if err != nil {
			panic(err)
		}
		numbers = append(numbers, int32(i))
	}

	fmt.Println()

	count := getPais(numbers, targetValue)
	fmt.Printf("Count: %v", count)

	fmt.Println()

	if err := scn.Err(); err != nil {
		fmt.Fprintln(os.Stderr, err)
	}

}
