import { useSearchRestaurant } from "@/api/RestaurantApi";
import CuisinesFilter from "@/components/CuisinesFilter";
import PaginationSelector from "@/components/PaginationSelector";
import SearchBar, { SearchForm } from "@/components/SearchBar";
import SearchResultCard from "@/components/SearchResultCard";
import SearchResultInfo from "@/components/SearchResultInfo";
import SortOptionDropdown from "@/components/SortOptionDropdown";
import { useState } from "react";
import { useParams } from "react-router-dom";

export type SearchState = {
  searchQuery: string;
  page: number;
  selectedCuisines: string[];
  sortOption: string;
};

const SearchPage = () => {
  const { city } = useParams();
  const [searchState, setSearchState] = useState<SearchState>({
    searchQuery: "",
    page: 1,
    selectedCuisines: [],
    sortOption: "bestMatch",
  });
  const [isExpanded, setIsExpanded] = useState<boolean>(false);

  const { results, isLoading } = useSearchRestaurant(searchState, city);

  function setSortOption (sortOption: string) {
    setSearchState((prevState) => ({
      ...prevState,
      sortOption,
      page: 1,
    }));
  };

  function setSelectedCuisines (selectedCuisines: string[]) {
    setSearchState((prevState) => ({
      ...prevState,
      selectedCuisines,
      page: 1,
    }));
  };

  function setPage (page: number) {
    setSearchState((prevState) => ({
      ...prevState,
      page,
    }));
  };

  function setSearchQuery (searchFormData: SearchForm) {
    setSearchState((prevState) => ({
      ...prevState,
      searchQuery: searchFormData.searchQuery,
    }));
  };

  function resetSearch () {
    setSearchState((prevState) => ({
      ...prevState,
      searchQuery: "",
      page: 1,
    }));
  };

  if (isLoading) {
    return <span>Loading...</span>;
  }

  if (!results?.data || !city) {
    console.log(!results?.data)
    return <span>No results found</span>;
  }

  return (
    <div className="grid grid-cols-1 lg:grid-cols-[250px_1fr] gap-5">
      <div id="cuisines-list">
        <CuisinesFilter
          selectedCuisines={searchState.selectedCuisines}
          onChange={setSelectedCuisines}
          isExpanded={isExpanded}
          onExpandedClick={() =>
            setIsExpanded((prevIsExpanded) => !prevIsExpanded)
          }
        />
      </div>
      <div id="main-content" className="flex flex-col gap-5">
        <SearchBar
          searchQuery={searchState.searchQuery}
          onSubmit={setSearchQuery}
          placeholder="Search by Cuisine or Restaurant Name"
          onReset={resetSearch}
        />
        <div className="flex flex-col lg:flex-row justify-between gap-3">
          <SearchResultInfo total={results.pagination.total} city={city} />
          <SortOptionDropdown
            sortOption={searchState.sortOption}
            onChange={(value) => setSortOption(value)}
          />
        </div>
        {results.data.map((restaurant) => (
          <SearchResultCard restaurant={restaurant} />
        ))}
        <PaginationSelector
          page={results.pagination.page}
          pages={results.pagination.pages}
          onPageChange={setPage}
        />
      </div>
    </div>
  );
};

export default SearchPage;
